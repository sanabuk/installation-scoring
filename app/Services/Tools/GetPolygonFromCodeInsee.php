<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GetPolygonFromCodeInsee
{
    public string $code_insee;

    public function __construct($code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke(): string
    {
        $base_url = "https://geo.api.gouv.fr/communes?code=".$this->code_insee."&format=geojson&geometry=contour";

        try {
            $response = retry(10,
                function ($attempt) use ($base_url) {
                    return Http::withHeaders([
                        'Content-Type' => 'application/json; charset=utf-8'
                        ])->get($base_url)->throw();
                },
                function ($attempt) {
                    return 1000*pow(2,$attempt);
                }
            );

            $data = $response->json();
            //TODO gestion des communes réparties sur plusieurs polygons
            if($data['features'][0]['geometry']['type'] === 'Polygon'){
                $data = $data['features'][0]['geometry']['coordinates'][0];
            } else {
                $data = $data['features'][0]['geometry']['coordinates'][0][0];
            }
            
            return $this->getBoundBox($data);
            
        } catch (\Exception $e) {
            Log::error('API gouv get error in GetPolygonFromCodeInsee class: ' . $e->getMessage());
            throw new \Exception('GEO API GOUV for code_insee '.$this->code_insee.' request failed with status: ' . $e->getMessage());
        }
    }

    private function getBoundBox(array $data)
    {
        $lat = array_column($data, 0);
        $lon = array_column($data, 1);

        $minLat = min($lat);
        $maxLat = max($lat);
        $minLon = min($lon);
        $maxLon = max($lon);

        return $minLon.' '.$minLat.' '.$minLon.' '.$maxLat.' '.$maxLon.' '.$maxLat.' '.$maxLon.' '.$minLat.' '.$minLon.' '.$minLat;
    }
}