<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetDataByPolygonFromOverpass
{
    public string $polygon_string;
    public string $amenity;
    public string $type;

    public function __construct(string $polygon_string, string $amenity, string $type)
    {
        $this->polygon_string = $polygon_string;
        $this->amenity = $amenity;
        $this->type = $type; // "node" or "way"
    }

    public function __invoke(): JsonResponse
    {
        $overpass_url = "https://overpass-api.de/api/interpreter";

        $query = sprintf(
            '[out:json];
            (
            '.$this->type.'["amenity"="'.$this->amenity.'"](poly:"' . $this->polygon_string . '");
            );
            out geom;'
        );

        try {
            $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
            ])->get($overpass_url,[
                'data' => $query
            ]);

            $datas_from_api = $response->json();
            $datas[$this->amenity] = [];
            foreach ($datas_from_api['elements'] ?? [] as $element) {
                if (isset($element['tags']['amenity']) && $element['tags']['amenity'] == $this->amenity) {
                    $datas[$this->amenity][] = $element;
                }
            }
            return response()->json(array_unique($datas[$this->amenity], SORT_REGULAR));
        } catch (\Exception $e) {
            Log::error('Overpass API from GetDataFromOverpass class: ' . $e->getMessage());
            throw new \Exception('Overpass API request failed with status: ' . $e->getMessage());
        }
        

    }
}