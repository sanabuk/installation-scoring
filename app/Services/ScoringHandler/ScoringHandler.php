<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyFarmService;
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
        // $recap = [];
        // $scrapNearbyMunicipalities = new GetNearbyMunicipalities($this->lat, $this->lon, 2);
        // $nearbyMunicipalities = $scrapNearbyMunicipalities();
        // dump($nearbyMunicipalities);
        // $recap = $this->getNearbyMunicipalities($nearbyMunicipalities, $recap);
        // dump($recap);

        // $scrapNearbyOrganicFarms = new GetNearbyOrganicVegetableFarms($this->lat, $this->lon, 2); 
        // $nearbyOrganicVegetableFarms = $scrapNearbyOrganicFarms();
        // $globalOragnicVegetablesFarms = json_decode($nearbyOrganicVegetableFarms->getContent());
        // dump($globalOragnicVegetablesFarms->nbTotal);
        $nearbyFarmService = new NearbyFarmService();
        $nearbyFarms = $nearbyFarmService->getNearbyFarms(['37261']);
        dump($nearbyFarms);
    }

    private function getNearbyMunicipalities(JsonResponse $nearbyMunicipalities, array $recap):array
    {
        foreach (json_decode($nearbyMunicipalities->getContent()) as $municipality) {
            $recap[$municipality->name]['population'] = $municipality->population;
            $recap[$municipality->name]['code_insee'] = $municipality->code_insee;
        }
        return $recap;
    }
}