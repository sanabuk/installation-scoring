<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyFarmService;
use App\Services\Concurrence\Service\NearbyOrganicVegetableFarmService;
use App\Services\Finance\DTO\IncomingTaxDTO;
use App\Services\Finance\Service\ExtraTaxService;
use App\Services\Finance\Service\IncomingTaxService;
use App\Services\Geographic\Scraper\GetNearbyCitiesByDuration;
use App\Services\Tools\ArrayHydrator;
use App\Services\Tools\GetCodeInseeFromLatAndLon;
use App\Services\Tools\GetIsochroneByDuration;
use App\Services\Tools\GetPolygonFromCodeInsee;
use App\Services\Tourism\Service\MarketplaceService;
use App\Services\Tourism\Service\RestaurantService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScoringHandler
{
    public float $lon;
    public float $lat;
    public string $email;

    const INCOMING_TAX_SCORING_THRESHOLDS = [
        'TAXABLE_HOUSEHOLDS_PERCENT' => 45.3,
        'AVERAGE_SALARY_TAX' => 75.85,
        'AVERAGE_PENSION_TAX' => 57.72
    ];
    
    public function __construct(float $lat, float $lon, string $email)
    {
        $this->lon = $lon;
        $this->lat = $lat;
        $this->email = $email;
    }

    public function handler()
    {
        try {
            $hydrator = new ArrayHydrator();
        
            $polygonIsochroneService = new GetIsochroneByDuration($this->lat, $this->lon, 15);
            $isochrones = $polygonIsochroneService->getIsochrone();
            $nearbyMunicipalities = [];
            foreach ($isochrones as $isochrone_feature) {
                $_cities = [];
                $isochrone = $isochrone_feature['geometry']['coordinates'][0];
                $interval = $isochrone_feature['properties']['value'];
                Log::info('Processing isochrone with interval: ' . $interval);
                if ($interval > 600) {
                    $split_isochrones = $polygonIsochroneService->splitIsochroneInto4($isochrone);
                } else {
                    $split_isochrones = [];
                    $polygon_string = '';
                    foreach ($isochrone as $point) {
                        $polygon_string .= $point[1].' '.$point[0].' ';
                    }
                    $polygon_string = rtrim($polygon_string);
                    $split_isochrones[] = $polygon_string;
                }
                
                
                foreach ($split_isochrones as $polygon_string) {
                    $scrapNearbyCitiesByDuration = new GetNearbyCitiesByDuration($polygon_string, $interval);
                    sleep(1); // To avoid Overpass API rate limit
                    $_cities[] = $scrapNearbyCitiesByDuration();
                }
                if ($interval > 600) {
                    $nearbyMunicipalities[] = $this->mergeUniqueByKeys(array_merge($_cities[0], $_cities[1],$_cities[2], $_cities[3]));
                } else {
                    $nearbyMunicipalities[] = $_cities[0];
                }
            }
            $nearbyMunicipalities = $this->mergeUniqueByKeys(array_merge($nearbyMunicipalities[0], $nearbyMunicipalities[1], $nearbyMunicipalities[2]));

            $codes_insee_array = $this->getAllCodeInsee($nearbyMunicipalities);
            dump($codes_insee_array);

            $nearbyFarmService = new NearbyFarmService();
            $nearbyFarms = $nearbyFarmService->getNearbyFarms($codes_insee_array);
            Log::info('Nearby farms');
            $_results = $hydrator->hydrate($nearbyMunicipalities, $nearbyFarms, 'code_insee', 'code_insee', 'nearby_farms');
            // dump($_results);

            $nearbyOrganicVegetableFarmService = new NearbyOrganicVegetableFarmService();
            $nearbyOrganicVegetableFarms = $nearbyOrganicVegetableFarmService->getNearbyOrganicVegetableFarms($this->lat, $this->lon, 15);
            Log::info('Nearby organic vegetable farms');
            $_results_with_organic_farms = $hydrator->hydrate($_results, $nearbyOrganicVegetableFarms, 'code_insee', 'code_insee', 'nearby_organic_vegetable_farms');
            // dump($_results_with_organic_farms);

            $extraTaxService = new ExtraTaxService();
            $extraTaxInfo = $extraTaxService->getExtraTax($codes_insee_array);
            Log::info('Extra tax info');
            $_results_with_extra_tax = $hydrator->hydrate($_results_with_organic_farms, $extraTaxInfo, 'code_insee', 'code_insee', 'extra_tax');
            // dump($_results_with_extra_tax);

            $incomingTaxInfos = [];
            foreach ($codes_insee_array as $code_insee) {
                $incomingTaxService = new IncomingTaxService();
                $incomingTaxInfos[] = $incomingTaxService->getIncomingTax($code_insee);
            }
            Log::info('Incoming tax info');
            $_results_with_incoming_tax = $hydrator->hydrate($_results_with_extra_tax, $incomingTaxInfos, 'code_insee', 'code_insee', 'incoming_tax');
            // dump($_results_with_incoming_tax);

            $restaurants = [];
            foreach ($codes_insee_array as $code_insee) {
                Log::info($code_insee);
                $get_polygon = new GetPolygonFromCodeInsee($code_insee);
                $polygon_string = $get_polygon();
                $scrapRestaurantsOffer = new RestaurantService();
                $new_restaurants = $scrapRestaurantsOffer->getRestaurants($polygon_string);
                $restaurants = array_merge($restaurants, $new_restaurants);
                //usleep(50*1000); // To avoid Overpass API rate limit
            }
            $restaurants = array_values(array_unique($restaurants,SORT_REGULAR));
            Log::info('Restaurants info');
            $_results_with_restaurants = $hydrator->hydrate($_results_with_incoming_tax, $restaurants, 'code_insee', 'code_insee', 'restaurants');

            $marketplaces = [];
            foreach ($codes_insee_array as $code_insee) {
                $get_polygon = new GetPolygonFromCodeInsee($code_insee);
                $polygon_string = $get_polygon();
                $scrapMarketplacesOffer = new MarketplaceService();
                $marketplaces = array_merge($marketplaces, $scrapMarketplacesOffer->getMarketplaces($polygon_string));
                //usleep(50*1000);  // To avoid Overpass API rate limit
            }
            $marketplaces = array_values(array_unique($marketplaces,SORT_REGULAR));
            Log::info('Marketplaces info');
            $_results_with_marketplaces = $hydrator->hydrate($_results_with_restaurants, $marketplaces, 'code_insee', 'code_insee', 'marketplaces');


            $code_insee = new GetCodeInseeFromLatAndLon((string)$this->lat, (string)$this->lon);
            $code_insee_str = $code_insee();
            Storage::put('scoring_results_'.$code_insee_str.'_'.$this->email.'.json', json_encode($_results_with_marketplaces));
        } catch (\Exception $e) {
            Log::error('Error in ScoringHandler: '.$e);
            throw $e;
        }

    }

    private function getAllCodeInsee($recap):array
    {
        $codes_insee = [];
        foreach ($recap as $cityInfo) {
            $codes_insee[] = $cityInfo['code_insee'];
        }
        return $codes_insee;
    }

    private function mergeUniqueByKeys(array $arrays): array
    {
        $result = [];

        foreach ($arrays as $item) {
            // Clé unique basée sur name + code_insee
            $key = $item['name'] . '|' . $item['code_insee'];

            // Si l'entrée existe déjà,
            // on garde celle ayant la plus petite limit_duration
            if (isset($result[$key])) {
                if ($item['limit_duration'] < $result[$key]['limit_duration']) {
                    $result[$key] = $item;
                }
            } else {
                $result[$key] = $item;
            }
        }

        // Retourne un tableau indexé propre
        return array_values($result);
    }

    private function scoringFromIncomingTax(IncomingTaxDTO $incomingTaxDTO)
    {
        $percent_taxable_households = $incomingTaxDTO->getNumberOfTaxedHouseholds()/$incomingTaxDTO->getNumberOfTaxableHouseholds() * 100;
        $scoring_percent_taxable_households = ($percent_taxable_households / self::INCOMING_TAX_SCORING_THRESHOLDS['TAXABLE_HOUSEHOLDS_PERCENT']) * 100;
        $average_salary_tax = $incomingTaxDTO->getAmountBySalary()/$incomingTaxDTO->getNumberOfHouseholdsTaxedOnSalary()*(100/$percent_taxable_households);
        $scoring_average_salary_tax = ($average_salary_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_SALARY_TAX']) * 100;
        $average_pension_tax = $incomingTaxDTO->getAmountByPension()/$incomingTaxDTO->getNumberOfHouseholdsTaxedOnPension()*(100/$percent_taxable_households);
        $scoring_average_pension_tax = ($average_pension_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_PENSION_TAX']) * 100;
        $scoring_incoming_tax = round(($scoring_percent_taxable_households + $scoring_average_salary_tax + $scoring_average_pension_tax) / 3,2);
        return [
            'code_insee' => $incomingTaxDTO->getCodeInsee(),
            'scoring_percent_taxable_households' => round($scoring_percent_taxable_households,2),
            'scoring_average_salary_tax' => round($scoring_average_salary_tax,2),
            'scoring_average_pension_tax' => round($scoring_average_pension_tax,2),
            'scoring_incoming_tax' => $scoring_incoming_tax
        ];
    }
}