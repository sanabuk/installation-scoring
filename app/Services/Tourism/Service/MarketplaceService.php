<?php

namespace App\Services\Tourism\Service;

use App\Services\Tools\GetCodeInseeFromLatAndLon;
use App\Services\Tourism\Scraper\GetMarketplacesOffer;
use App\Services\Tourism\DTO\MarketplaceDTO;
use Illuminate\Support\Facades\Log;

class MarketplaceService
{
    public function getMarketplaces(string $postal_code)
    {
        try {
            $marketplaceFromCsv = new GetMarketplacesOffer($postal_code);
            $rawDatas = $marketplaceFromCsv();
            return $this->mapToMarketplaceDTO($rawDatas);
        } catch (\Exception $e) {
            Log::error('Error in MarketplaceService class : ' . $e->getMessage());
            throw new \Exception('Error Marketplace Service : ' . $e->getMessage());
        }
        
    }

    private function mapToMarketplaceDTO($rawDatas)
    {
        $marketplaceList = [];
        foreach ($rawDatas as $data) {
            $marketplace_DTO = new MarketplaceDTO();
            $marketplace_DTO->setName($data['nom_marche'] ?? 'Unknown');
            $marketplace_DTO->setCity($data['commune'] ?? null);
            $marketplace_DTO->setPostcode($data['code_postal'] ?? null);
            $marketplace_DTO->setWebsite($data['url'] ?? null);
            $marketplace_DTO->setHoraires($data['horaires'] ?? null);
            $marketplaceList[] = $marketplace_DTO;
        }
        
        return $marketplaceList;
    }
}