<?php 

namespace App\Services\Geographic\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetNearbyMunicipalities 
{
    public float $lat;
    public float $lon;
    public int $radius_km;

    public function __construct(float $lat, float $lon, int $radius_km = 15)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->radius_km = $radius_km;
    }

    public function __invoke():JsonResponse
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
            $this->radius_km * 1000, $this->lat, $this->lon,
            $this->radius_km * 1000, $this->lat, $this->lon
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
                if (isset($element['tags']['name']) && isset($element['tags']['ref:INSEE']) && isset($element['tags']['population'])) {
                    $communes[] = [
                        'name' => $element['tags']['name'],
                        'code_insee' => $element['tags']['ref:INSEE'],
                        'population' => $element['tags']['population']
                    ];
                }
            }
            return response()->json(array_unique($communes, SORT_REGULAR));
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}