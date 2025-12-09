<?php

namespace App\Services\Tourism\Service;

use App\Services\Tools\GetCodeInseeFromLatAndLon;
use App\Services\Tourism\Scraper\GetMarketplacesOffer;
use App\Services\Tourism\DTO\MarketplaceDTO;
use Illuminate\Support\Facades\Log;

class MarketplaceService
{
    public function getMarketplaces(string $polygon_string): array
    {
        try {
            $getMarketplaceFromApi = new GetMarketplacesOffer($polygon_string);
            $rawDatas = $getMarketplaceFromApi();
            return array_map(
                fn($rawData) => $this->mapToMarketplaceDTO($rawData), json_decode($rawDatas->getContent())
            );
        } catch (\Exception $e) {
            Log::error('Error in MarketplaceService class : ' . $e->getMessage());
            throw new \Exception('Error Marketplace Service : ' . $e->getMessage());
        }
        
    }

    private function mapToMarketplaceDTO($rawData)
    {
        $code_insee = $this->getCodeInseeFromLatAndLon(
            $rawData->lat ?? $rawData->geometry[0]->lat ?? 0,
            $rawData->lon ?? $rawData->geometry[0]->lon ?? 0
        );
        $marketplace_DTO = new MarketplaceDTO();
        $marketplace_DTO->setName($rawData->tags->name ?? 'Unknown');
        $marketplace_DTO->setAddress($rawData->tags->{'addr:street'} ?? null);
        $marketplace_DTO->setCity($rawData->tags->{'addr:city'} ?? null);
        $marketplace_DTO->setPostcode($rawData->tags->{'addr:postcode'} ?? null);
        $marketplace_DTO->setWebsite($rawData->tags->website ?? null);
        $marketplace_DTO->setLat($rawData->lat ?? $rawData->geometry[0]->lat ?? null);
        $marketplace_DTO->setLon($rawData->lon ?? $rawData->geometry[0]->lon ??null);
        $marketplace_DTO->setCodeInsee($code_insee);
        return $marketplace_DTO;
    }

    private function getCodeInseeFromLatAndLon(float $lat, float $lon): ?string
    {
        $tool = new GetCodeInseeFromLatAndLon((string)$lat, (string)$lon);
        return $tool();
    }
}