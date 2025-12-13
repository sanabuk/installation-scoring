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
            $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
            ])->get($base_url);

            $data = $response->json();
            //TODO gestion des communes réparties sur plusieurs polygons
            if(count($data['features'][0]['geometry']['coordinates']) === 1){
                $data = $data['features'][0]['geometry']['coordinates'][0];
            } else {
                $data = $data['features'][0]['geometry']['coordinates'][0][0];
            }
            
            return $this->getBoundBox($data);
            // $polygon = [];
            // foreach ($data as $coord) {
            //     $polygon[] = $coord[1] . ' ' . $coord[0];
            // }

            // return implode(' ', $polygon);
        } catch (\Exception $e) {
            Log::error('API gouv get error in GetPolygonFromCodeInsee class: ' . $e->getMessage());
            throw new \Exception('Overpass API request failed with status: ' . $e->getMessage());
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