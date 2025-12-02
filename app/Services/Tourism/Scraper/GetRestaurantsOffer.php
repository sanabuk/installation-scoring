<?php

namespace App\Services\Tourism\Scraper;

use App\Services\Tools\GetDataFromOverpass;
use Illuminate\Support\Facades\Log;

class GetRestaurantsOffer
{
    private string $polygon_string;

    public function __construct(string $polygon_string)
    {
        $this->polygon_string = $polygon_string;
    }

    public function __invoke()
    {
        try {
            $call_overpass_node = new GetDataFromOverpass($this->polygon_string, 'restaurant', 'node');
            $restaurants_node = $call_overpass_node();
            $call_overpass_way = new GetDataFromOverpass($this->polygon_string, 'restaurant', 'way');
            $restaurants_way = $call_overpass_way();
            return response()->json(array_merge(
                json_decode($restaurants_node->getContent(), true),
                json_decode($restaurants_way->getContent(), true)
            ));
        } catch (\Exception $e) {
            Log::error('Error from GetRestaurantsOffer class: ' . $e->getMessage());
            throw new \Exception('Error GetRestaurantsOffer class : ' . $e->getMessage());
        }
        
    }
}