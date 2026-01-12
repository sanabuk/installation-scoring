<?php 

namespace App\Services\ScoringHandler;

use App\Mail\ScoringReadyMail;
use App\Services\Concurrence\Service\AmapService;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ScoringHandler
{
    public float $lon;
    public float $lat;
    public string $email;

    const INCOMING_TAX_SCORING_THRESHOLDS = [
        'TAXABLE_HOUSEHOLDS_PERCENT' => 45.3,
        'AVERAGE_SALARY_TAX' => 34.35,
        'AVERAGE_PENSION_TAX' => 26.14
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

            // $nearbyFarmService = new NearbyFarmService();
            // $nearbyFarms = $nearbyFarmService->getNearbyFarms($codes_insee_array);
            // Log::info('Nearby farms');
            // $_results = $hydrator->hydrate($nearbyMunicipalities, $nearbyFarms, 'code_insee', 'code_insee', 'nearby_farms');
            // dump($_results);

            $nearbyOrganicVegetableFarmService = new NearbyOrganicVegetableFarmService();
            $nearbyOrganicVegetableFarms = $nearbyOrganicVegetableFarmService->getNearbyOrganicVegetableFarms($this->lat, $this->lon, 15);
            $nearbyOrganicVegetableFarms = array_values(array_unique($nearbyOrganicVegetableFarms, SORT_REGULAR));
            Log::info('Nearby organic vegetable farms');
            $_results_with_organic_farms = $hydrator->hydrate($nearbyMunicipalities, $nearbyOrganicVegetableFarms, 'code_insee', 'code_insee', 'nearby_organic_vegetable_farms');
            // dump($_results_with_organic_farms);

            // $extraTaxService = new ExtraTaxService();
            // $extraTaxInfo = $extraTaxService->getExtraTax($codes_insee_array);
            // Log::info('Extra tax info');
            // $_results_with_extra_tax = $hydrator->hydrate($_results_with_organic_farms, $extraTaxInfo, 'code_insee', 'code_insee', 'extra_tax');
            // dump($_results_with_extra_tax);

            $incomingTaxInfos = [];
            foreach ($codes_insee_array as $code_insee) {
                $incomingTaxService = new IncomingTaxService();
                $new_incoming_tax = $incomingTaxService->getIncomingTax($code_insee);
                $incomingTaxInfos[] = $this->scoringFromIncomingTax($new_incoming_tax);
            }
            Log::info('Incoming tax info');
            $_results_with_incoming_tax = $hydrator->hydrate($_results_with_organic_farms, $incomingTaxInfos, 'code_insee', 'code_insee', 'incoming_tax');
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
            $_global_results = $hydrator->hydrate($_results_with_restaurants, $marketplaces, 'code_insee', 'code_insee', 'marketplaces');

            $amap = [];
            foreach ($nearbyMunicipalities as $key => $value) {
                $amap_service = new AmapService();
                $results = $amap_service->getAmap(substr($value['code_insee'],0,2), strtolower($value['name']));
                $amap = array_merge($amap, $results);
            }
            $global_results['cities'] = $hydrator->hydrate($_global_results, $amap, 'name', 'city', 'amap');

            $global_results['scoring']['population_totale'] = array_reduce($global_results['cities'], function ($carry, $item) {
                return $carry + ($item['population'] ?? 0);
            }, 0);

            $global_results['scoring']['demande_locale'] = $this->getScoreDemandeLocale($global_results['cities']);

            $global_results['scoring']['concurrence'] = $this->getScoreConcurrence($global_results['scoring']['population_totale'], $global_results['cities']);

            $get_code_insee_from_lat_and_lon = new GetCodeInseeFromLatAndLon((string)$this->lat, (string)$this->lon);
            $code_insee = $get_code_insee_from_lat_and_lon() ?? 'unknown';
            $hash = $this->generateFilenameFromEmailAndCoordinates($this->email, $this->lat, $this->lon);
            Storage::put($code_insee.'-'.$hash.'.json', json_encode($global_results));
            $this->sendMail($this->email, $code_insee, $hash);
            Log::info('Scoring process completed successfully.');
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

        $percent_taxable_households = $incomingTaxDTO->getNumberOfTaxableHouseholds() !== 0 ? $incomingTaxDTO->getNumberOfTaxedHouseholds()/$incomingTaxDTO->getNumberOfTaxableHouseholds() * 100: 0;
        $scoring_percent_taxable_households = ($percent_taxable_households / self::INCOMING_TAX_SCORING_THRESHOLDS['TAXABLE_HOUSEHOLDS_PERCENT']) * 100;
        $average_salary_tax = $incomingTaxDTO->getNumberOfHouseholdsTaxedOnSalary() !== 0 ? $incomingTaxDTO->getAmountBySalary()/$incomingTaxDTO->getNumberOfHouseholdsTaxedOnSalary(): 0;
        $scoring_average_salary_tax = ($average_salary_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_SALARY_TAX']) * 100;
        $average_pension_tax = $incomingTaxDTO->getNumberOfHouseholdsTaxedOnPension() !== 0 ? $incomingTaxDTO->getAmountByPension()/$incomingTaxDTO->getNumberOfHouseholdsTaxedOnPension(): 0;
        $scoring_average_pension_tax = ($average_pension_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_PENSION_TAX']) * 100;
        $scoring_incoming_tax = round(($scoring_percent_taxable_households + $scoring_average_salary_tax + $scoring_average_pension_tax) / 3,2);
        $incoming_tax = [
            'number_of_taxable_households' => $incomingTaxDTO->getNumberOfTaxableHouseholds(),
            'scoring_percent_taxable_households' => round($scoring_percent_taxable_households,2),
            'scoring_percent_taxable_households_color' => $this->colorFromScore(round($scoring_percent_taxable_households,2)),
            'scoring_average_salary_tax' => round($scoring_average_salary_tax,2),
            'scoring_average_salary_tax_color' => $this->colorFromScore(round($scoring_average_salary_tax,2)),
            'scoring_average_pension_tax' => round($scoring_average_pension_tax,2),
            'scoring_average_pension_tax_color' => $this->colorFromScore(round($scoring_average_pension_tax,2)),
            'scoring_incoming_tax' => $scoring_incoming_tax,
            'scoring_incoming_tax_color' => $this->colorFromScore($scoring_incoming_tax),
            'code_insee' => $incomingTaxDTO->getCodeInsee()
        ];
        return $incoming_tax;
    }

    private function colorFromScore(float $score): string
    {
        $score = max(0, min(150, $score));

        // Points clés
        $red     = [255, 0,   0];
        $orange  = [255, 145, 0];
        $light_green = [0, 255, 0];
        $green   = [65,   173, 84];

        if ($score <= 75) {
            // Dégradé Rouge → Orange
            $ratio = ($score - 100) / 100;

            $r = (int)($red[0]   + ($orange[0] - $red[0])   * $ratio);
            $g = (int)($red[1]   + ($orange[1] - $red[1])   * $ratio);
            $b = (int)($red[2]   + ($orange[2] - $red[2])   * $ratio);

        } 
        if ($score <= 100) {
            // Dégradé Orange → Vert Clair
            $ratio = ($score - 75) / 75;

            $r = (int)($orange[0] + ($light_green[0] - $orange[0]) * $ratio);
            $g = (int)($orange[1] + ($light_green[1] - $orange[1]) * $ratio);
            $b = (int)($orange[2] + ($light_green[2] - $orange[2]) * $ratio);

        }else {
            // Dégradé Vert Clair → Vert
            $ratio = ($score - 50) / 75;

            $r = (int)($light_green[0] + ($green[0] - $light_green[0]) * $ratio);
            $g = (int)($light_green[1] + ($green[1] - $light_green[1]) * $ratio);
            $b = (int)($light_green[2] + ($green[2] - $light_green[2]) * $ratio);
        }

        return "rgb($r, $g, $b)";
    }

    private function generateFilenameFromEmailAndCoordinates(string $email, float $lat, float $lon): string
    {
        $string = $email . $lon . $lat . microtime(true);
        return substr(hash('sha256', $string), 0, 16);
    }

    public function sendMail(string $email, ?string $code_insee, ?string $hash): void
    {
        $code_insee = $code_insee ?? 'unknown';
        Mail::to($email)->send(new ScoringReadyMail(env('APP_URL')."/scoring-result/".$code_insee."/".$hash));
    }

    private function getScoreDemandeLocale(array $cities): string
    {
        $total_foyers_imposables = 0;
        $incoming_tax_score_cumulated = 0;
        foreach ($cities as $city)
        {
            $total_foyers_imposables += $city['incoming_tax'][0][0]['number_of_taxable_households'] ?? 0;            
            $incoming_tax_score_cumulated += $city['incoming_tax'][0][0]['scoring_incoming_tax'] * ($city['incoming_tax'][0][0]['number_of_taxable_households'] ?? 0);
        }
        $scoreDemandeLocale = round($incoming_tax_score_cumulated / $total_foyers_imposables, 2);
        switch($scoreDemandeLocale){
            case $scoreDemandeLocale >= 100:
                return 'Excellent : '.$scoreDemandeLocale;
            case $scoreDemandeLocale >= 90:
                return 'Bon : '.$scoreDemandeLocale;
            case $scoreDemandeLocale >= 70:
                return 'Moyen : '.$scoreDemandeLocale;
            case $scoreDemandeLocale >= 40:
                return 'Faible : '.$scoreDemandeLocale;
            default:
                return 'Très faible : '.$scoreDemandeLocale;
        }
    }

    private function getScoreConcurrence(int $population_totale, array $cities): string
    {
        $number_of_nearby_organic_vegetable_farms = 0;
        foreach ($cities as $city)
        {
            $count = count($city['nearby_organic_vegetable_farms']) > 0 ? count($city['nearby_organic_vegetable_farms'][0]) : 0;
            $number_of_nearby_organic_vegetable_farms += $count;
        }
        $ratio = round(($number_of_nearby_organic_vegetable_farms / $population_totale) * 4974,2); //  1 ferme bio pour 4974 habitants
        switch($ratio){
            case $ratio >= 1:
                return 'Forte : '.$ratio;
            case $ratio >= 0.8:
                return 'Moyenne : '.$ratio;
            case $ratio >= 0.5:
                return 'Faible : '.$ratio;
            default:
                return 'Très faible : '.$ratio;
        }
    }
}