<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Geographic\Scraper\GetNearbyMunicipalities;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScoringHandler
{
    public float $lon;
    public float $lat;
    
    public function __construct(float $lat, float $lon)
    {
        $this->lon = $lon;
        $this->lat = $lat;
    }

    public function handler()
    {
        $scrapNearbyMunicipalities = new GetNearbyMunicipalities($this->lat, $this->lon, 2);
        $nearbyMunicipalities = $scrapNearbyMunicipalities();
        //dump($nearbyMunicipalities);
        $globalPopulation = $this->getGlobalPopulation($nearbyMunicipalities);
        dump($globalPopulation);

        $scrapNearbyOrganicFarms = new GetNearbyOrganicVegetableFarms($this->lat, $this->lon, 2); 
        $nearbyOrganicVegetableFarms = $scrapNearbyOrganicFarms();
        $globalOragnicVegetablesFarms = json_decode($nearbyOrganicVegetableFarms->getContent());
        dump($globalOragnicVegetablesFarms->nbTotal);
    }

    private function getGlobalPopulation(JsonResponse $nearbyMunicipalities):int
    {
        $globalPopulation = 0;
        foreach (json_decode($nearbyMunicipalities->getContent()) as $municipality) {
            $globalPopulation += $municipality->population;
        }
        return $globalPopulation;
    }
}