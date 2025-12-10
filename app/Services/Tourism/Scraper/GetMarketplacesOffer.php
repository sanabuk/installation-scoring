<?php

namespace App\Services\Tourism\Scraper;

use App\Services\Tools\GetDataByPolygonFromOverpass;
use Illuminate\Support\Facades\Log;

class GetMarketplacesOffer
{
    private string $polygon_string;

    public function __construct(string $polygon_string)
    {
        $this->polygon_string = $polygon_string;
    }

    public function __invoke()
    {
        try {
            $call_overpass_node = new GetDataByPolygonFromOverpass($this->polygon_string, 'marketplace', 'node');
            $marketplaces_node = $call_overpass_node();
            $call_overpass_way = new GetDataByPolygonFromOverpass($this->polygon_string, 'marketplace', 'way');
            $marketplaces_way = $call_overpass_way();
            return response()->json(array_merge(
                json_decode($marketplaces_node->getContent(), true),
                json_decode($marketplaces_way->getContent(), true)
            ));
        } catch (\Exception $e) {
            Log::error('Error from GetMarketplacesOffer class: ' . $e->getMessage());
            throw new \Exception('Error GetMarketplacesOffer class : ' . $e->getMessage());
        }
        
    }
}