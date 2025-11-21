<?php 

namespace App\Services\Concurrence\Service;

use App\Services\Concurrence\DTO\NearbyOrganicVegetableFarmDTO;
use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use DateTime;
use stdClass;

class NearbyOrganicVegetableFarmService
{
    public function getNearbyOrganicVegetableFarms(float $lat,float $lon,?int $radius): array
    {
        $scraper = new GetNearbyOrganicVegetableFarms($lat, $lon, $radius);
        $response = $scraper();
        $data = json_decode($response->getContent(), true);
        
        return array_values(array_filter(
            array_map(fn($rawData) => $this->mapToNearbyOrganicVegetableFarmDTO($rawData), $data['items']),
            fn($v) => $v !== null
        ));
    }

    private function mapToNearbyOrganicVegetableFarmDTO(array $rawData):?NearbyOrganicVegetableFarmDTO
    {
        $check_activity = false;
        foreach ($rawData['annuaireActivites'] as $value) {
            if ($value['id'] == 18){
                $check_activity = true;
                break;
            }
        }
        if(!$check_activity){
            return null;
        }
        $nearbyOrganicVegetableFarmDTO = new NearbyOrganicVegetableFarmDTO;
        $nearbyOrganicVegetableFarmDTO->setName($rawData['nom']);
        $nearbyOrganicVegetableFarmDTO->setNameAnnuaire($rawData['nomAnnuaire']);
        $nearbyOrganicVegetableFarmDTO->setSiret($rawData['siret']);
        $nearbyOrganicVegetableFarmDTO->setDatePremierEngagement(new DateTime($rawData['datePremierEngagement']));
        $nearbyOrganicVegetableFarmDTO->setPhone1($rawData['telephone']);
        $nearbyOrganicVegetableFarmDTO->setPhone2($rawData['telephoneNational']);
        $nearbyOrganicVegetableFarmDTO->setResponsable($rawData['gerant']);
        $nearbyOrganicVegetableFarmDTO->setAddress1($rawData['adressesOperateurs'][0]['lieu'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setZipcode1($rawData['adressesOperateurs'][0]['codePostal'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setCity1($rawData['adressesOperateurs'][0]['ville'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setAddress2($rawData['adressesOperateurs'][1]['lieu'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setZipcode2($rawData['adressesOperateurs'][1]['codePostal'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setCity2($rawData['adressesOperateurs'][1]['ville'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setUrl($rawData['siteWebs'][0]['url'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setLon($rawData['adressesOperateurs'][0]['location']['lon'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setLat($rawData['adressesOperateurs'][0]['location']['lat'] ?? null);
        $nearbyOrganicVegetableFarmDTO->setVenteProsGros($rawData['annuaireInformation']['venteProsGros']);
        $nearbyOrganicVegetableFarmDTO->setVenteProsDetails($rawData['annuaireInformation']['venteProsDetail']);
        $nearbyOrganicVegetableFarmDTO->setVenteParticuliers($rawData['annuaireInformation']['venteParticuliers']);
        $nearbyOrganicVegetableFarmDTO->setVenteRestoCollective($rawData['annuaireInformation']['venteRestauCollective']);
        $nearbyOrganicVegetableFarmDTO->setVenteRestoActivity($rawData['annuaireInformation']['venteRestauCommerciale']);
        $nearbyOrganicVegetableFarmDTO->setDistance($rawData['adresseOperateur']['distance']);
        return $nearbyOrganicVegetableFarmDTO;
    }
}