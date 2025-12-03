<?php 

namespace App\Services\Concurrence\Service;

use Illuminate\Support\Facades\Log;
use App\Services\Concurrence\DTO\NearbyFarmDTO;
use App\Services\Concurrence\Scraper\GetNearbyFarms;
use stdClass;

class NearbyFarmService
{
    public function getNearbyFarms(array $codes_insee): array
    {
        try {
            $getNearbyFarmsFromApi = new GetNearbyFarms($codes_insee);
            $rawDatas = $getNearbyFarmsFromApi();
            return array_map(
                fn($rawData) => $this->mapToNearbyFarmDTO($rawData), json_decode($rawDatas->getContent())->data
            );
        } catch (\Exception $e) {
            Log::error('Error in NearbyFarmService class: ' . $e->getMessage());
            throw $e;
        }
        
    }

    private function mapToNearbyFarmDTO(stdClass $rawData):NearbyFarmDTO
    {
        $nearbyFarmDTO = new NearbyFarmDTO;
        $nearbyFarmDTO->setCodeInsee($rawData->geocode_commune);
        $nearbyFarmDTO->setQuantity($rawData->valeur);
        $nearbyFarmDTO->setMunicipalityName($rawData->libelle_commune);
        $nearbyFarmDTO->setYear(intval(substr($rawData->date_mesure,0,4)));
        return $nearbyFarmDTO;
    }
}