<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyFarmService;
use App\Services\Concurrence\Service\NearbyOrganicVegetableFarmService;
use App\Services\Finance\Service\ExtraTaxService;
use App\Services\Finance\Service\IncomingTaxService;
use App\Services\Geographic\Scraper\GetNearbyCitiesByDuration;
use App\Services\Tools\ArrayHydrator;
use App\Services\Tools\GetCodeInseeFromLatAndLon;
use App\Services\Tools\GetIsochroneByDuration;
use App\Services\Tourism\Service\RestaurantService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScoringHandler
{
    public float $lon;
    public float $lat;
    public string $email;
    
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
            $isochrone = $polygonIsochroneService->getIsochrone();
            $split_isochrones = $polygonIsochroneService->splitIsochrone($isochrone);

            $_cities = [];
            foreach ($split_isochrones as $polygon_string) {
                $scrapNearbyCitiesByDuration = new GetNearbyCitiesByDuration($polygon_string);
                $_cities[] = $scrapNearbyCitiesByDuration();
            }
            $nearbyMunicipalities = array_values(array_unique(array_merge($_cities[0], $_cities[1]), SORT_REGULAR));
            //dump($nearbyMunicipalities);

            $codes_insee_array = $this->getAllCodeInsee($nearbyMunicipalities);
            // dump($codes_insee_array);

            $nearbyFarmService = new NearbyFarmService();
            $nearbyFarms = $nearbyFarmService->getNearbyFarms($codes_insee_array);
            $_results = $hydrator->hydrate($nearbyMunicipalities, $nearbyFarms, 'code_insee', 'code_insee', 'nearby_farms');
            // dump($_results);

            $nearbyOrganicVegetableFarmService = new NearbyOrganicVegetableFarmService();
            $nearbyOrganicVegetableFarms = $nearbyOrganicVegetableFarmService->getNearbyOrganicVegetableFarms($this->lat, $this->lon, 15);
            $_results_with_organic_farms = $hydrator->hydrate($_results, $nearbyOrganicVegetableFarms, 'code_insee', 'code_insee', 'nearby_organic_vegetable_farms');
            // dump($_results_with_organic_farms);

            $extraTaxService = new ExtraTaxService();
            $extraTaxInfo = $extraTaxService->getExtraTax($codes_insee_array);
            $_results_with_extra_tax = $hydrator->hydrate($_results_with_organic_farms, $extraTaxInfo, 'code_insee', 'code_insee', 'extra_tax');
            // dump($_results_with_extra_tax);

            $incomingTaxInfos = [];
            foreach ($codes_insee_array as $code_insee) {
                $incomingTaxService = new IncomingTaxService();
                $incomingTaxInfos[] = $incomingTaxService->getIncomingTax($code_insee);
            }
            $_results_with_incoming_tax = $hydrator->hydrate($_results_with_extra_tax, $incomingTaxInfos, 'code_insee', 'code_insee', 'incoming_tax');
            // dump($_results_with_incoming_tax);

            $restaurants = [];
            foreach ($split_isochrones as $polygon_string) {
                $scrapRestaurantsOffer = new RestaurantService($polygon_string);
                $restaurants = array_merge($restaurants, $scrapRestaurantsOffer->getRestaurants($polygon_string));
            }
            $_results_with_restaurants = $hydrator->hydrate($_results_with_incoming_tax, $restaurants, 'code_insee', 'code_insee', 'restaurants');
            $code_insee = new GetCodeInseeFromLatAndLon((string)$this->lat, (string)$this->lon);
            $code_insee_str = $code_insee();
            Storage::put('scoring_results_'.$code_insee_str.'_'.$this->email.'.json', json_encode($_results_with_restaurants));
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
}