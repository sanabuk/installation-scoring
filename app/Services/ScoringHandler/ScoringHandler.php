<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyFarmService;
use App\Services\Concurrence\Service\NearbyOrganicVegetableFarmService;
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
        $recap = [];
        $scrapNearbyMunicipalities = new GetNearbyMunicipalities($this->lat, $this->lon, 15);
        $nearbyMunicipalities = $scrapNearbyMunicipalities();
        $recap = $this->getNearbyMunicipalities($nearbyMunicipalities, $recap);
        dump($recap);

        $codes_insee_array = $this->getAllCodeInsee($recap);

        $nearbyFarmService = new NearbyFarmService();
        $nearbyFarms = $nearbyFarmService->getNearbyFarms($codes_insee_array);
        dump($nearbyFarms);

        $nearbyOrganicVegetableFarmService = new NearbyOrganicVegetableFarmService();
        $nearbyOrganicVegetableFarms = $nearbyOrganicVegetableFarmService->getNearbyOrganicVegetableFarms($this->lat, $this->lon, 15);
        dump($nearbyOrganicVegetableFarms);

        
    }

    private function getNearbyMunicipalities(JsonResponse $nearbyMunicipalities, array $recap):array
    {
        foreach (json_decode($nearbyMunicipalities->getContent()) as $municipality) {
            $recap[$municipality->name]['population'] = $municipality->population;
            $recap[$municipality->name]['code_insee'] = $municipality->code_insee;
        }
        return $recap;
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