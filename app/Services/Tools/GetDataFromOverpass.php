<?php

namespace App\Services\Tools;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Nette\Utils\Json;

class GetDataFromOverpass
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

        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
        ])->get($overpass_url,[
            'data' => $query
        ]);

        if ($response->successful()) {
            $datas_from_api = $response->json();
            $datas[$this->amenity] = [];
            foreach ($datas_from_api['elements'] ?? [] as $element) {
                dump($element);
                if (isset($element['tags']['amenity']) && $element['tags']['amenity'] == $this->amenity) {
                    $datas[$this->amenity][] = $element['tags'];
                }
            }
            return response()->json(array_unique($datas[$this->amenity], SORT_REGULAR));
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}