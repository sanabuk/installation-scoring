<?php

namespace App\Services\Tourism\Service;

use App\Services\Tools\GetCodeInseeFromLatAndLon;
use App\Services\Tourism\Scraper\GetRestaurantsOffer;
use App\Services\Tourism\DTO\RestaurantDTO;
use Illuminate\Support\Facades\Log;

class RestaurantService
{
    public function getRestaurants(string $polygon_string): array
    {
        try {
            $getRestaurantFromApi = new GetRestaurantsOffer($polygon_string);
            $rawDatas = $getRestaurantFromApi();
            return array_map(
                fn($rawData) => $this->mapToRestaurantDTO($rawData), json_decode($rawDatas->getContent())
            );
        } catch (\Exception $e) {
            Log::error('Error in RestaurantService class : ' . $e->getMessage());
            throw new \Exception('Error Restaurant Service : ' . $e->getMessage());
        }
        
    }

    private function mapToRestaurantDTO($rawData)
    {
        $code_insee = $this->getCodeInseeFromLatAndLon(
            $rawData->lat ?? $rawData->geometry[0]->lat ?? 0,
            $rawData->lon ?? $rawData->geometry[0]->lon ?? 0
        );
        $restaurant_DTO = new RestaurantDTO();
        $restaurant_DTO->setName($rawData->tags->name ?? 'Unknown');
        $restaurant_DTO->setCuisine($rawData->tags->cuisine ?? null);
        $restaurant_DTO->setAddress($rawData->tags->{'addr:street'} ?? null);
        $restaurant_DTO->setCity($rawData->tags->{'addr:city'} ?? null);
        $restaurant_DTO->setPostcode($rawData->tags->{'addr:postcode'} ?? null);
        $restaurant_DTO->setWebsite($rawData->tags->website ?? null);
        $restaurant_DTO->setPhone($rawData->tags->phone ?? null);
        $restaurant_DTO->setLat($rawData->lat ?? $rawData->geometry[0]->lat ?? null);
        $restaurant_DTO->setLon($rawData->lon ?? $rawData->geometry[0]->lon ??null);
        $restaurant_DTO->setCodeInsee($code_insee);
        return $restaurant_DTO;
    }

    private function getCodeInseeFromLatAndLon(float $lat, float $lon): ?string
    {
        $tool = new GetCodeInseeFromLatAndLon((string)$lat, (string)$lon);
        return $tool();
    }
}