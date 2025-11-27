<?php 

namespace App\Services\ScoringHandler;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyFarmService;
use App\Services\Concurrence\Service\NearbyOrganicVegetableFarmService;
use App\Services\Finance\Service\ExtraTaxService;
use App\Services\Finance\Service\IncomingTaxService;
use App\Services\Geographic\Scraper\GetNearbyCitiesByDuration;
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
        $scrapNearbyCitiesByDuration = new GetNearbyCitiesByDuration($this->lat, $this->lon, 15);
        $nearbyMunicipalities = $scrapNearbyCitiesByDuration();
        dump($nearbyMunicipalities);

        $codes_insee_array = $this->getAllCodeInsee($nearbyMunicipalities);
        dump($codes_insee_array);

        $nearbyFarmService = new NearbyFarmService();
        $nearbyFarms = $nearbyFarmService->getNearbyFarms($codes_insee_array);
        dump($nearbyFarms);

        $nearbyOrganicVegetableFarmService = new NearbyOrganicVegetableFarmService();
        $nearbyOrganicVegetableFarms = $nearbyOrganicVegetableFarmService->getNearbyOrganicVegetableFarms($this->lat, $this->lon, 15);
        dump($nearbyOrganicVegetableFarms);

        $extraTaxService = new ExtraTaxService();
        $extraTaxInfo = $extraTaxService->getExtraTax($codes_insee_array);
        dump($extraTaxInfo);

        foreach ($codes_insee_array as $code_insee) {
            $incomingTaxService = new IncomingTaxService();
            $incomingTaxInfo = $incomingTaxService->getIncomingTax($code_insee);
            dump($incomingTaxInfo);
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