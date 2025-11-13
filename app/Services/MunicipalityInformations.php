<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class MunicipalityInformations
{
    public function getNearbyMunicipalities(float $lat, float $lon, int $radius_km=5):JsonResponse
    {
        $overpass_url = "https://overpass-api.de/api/interpreter";

        $query = sprintf(
            "[out:json];
            (
              way[\"admin_level\"=\"8\"][\"boundary\"=\"administrative\"][name](around:%d,%s,%s);
              relation[\"admin_level\"=\"8\"][\"boundary\"=\"administrative\"][name](around:%d,%s,%s);
            );
            out body;
            >;
            out skel qt;",
            $radius_km * 1000, $lat, $lon,
            $radius_km * 1000, $lat, $lon
        );

        $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8'
            ])->get($overpass_url, [
                'data' => $query
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $communes = [];
            foreach ($data['elements'] ?? [] as $element) {
                if (isset($element['tags']['name']) && isset($element['tags']['ref:INSEE'])) {
                    $communes[] = [
                        'name' => $element['tags']['name'],
                        'code_insee' => $element['tags']['ref:INSEE']
                    ];
                }
            }
            return response()->json(array_unique($communes, SORT_REGULAR));
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }

    public function getPopulation(string $code_insee)
    {
        $response = Http::get('https://geo.api.gouv.fr/communes/'.$code_insee);

        if ($response->successful()) {
            $data = $response->json();
            return $data['population'];
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}