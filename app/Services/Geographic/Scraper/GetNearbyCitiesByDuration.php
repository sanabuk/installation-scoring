<?php 

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class GetNearbyCitiesByDuration
{
    public float $lat;
    public float $lon;
    public int $duration; //in minutes

    public function __construct(float $lat, float $lon, int $duration)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->duration = $duration*60;
    }

    public function __invoke():JsonResponse
    {
        return $this->getIsochrone();
    }

    private function getIsochrone():JsonResponse
    {
        $url = 'https://api.openrouteservice.org/v2/isochrones/driving-car';
        $response = Http::withHeaders([
            'Authorization' => env('OPEN_ROUTE_SERVICE_API_KEY')
        ])->post($url,[
            'locations' => [[$this->lon,$this->lat]],
            'range' => [$this->duration]
        ]);
        $isochroneData = json_decode($response, true);
        $polygon = $isochroneData['features'][0]['geometry']['coordinates'][0];

        $polygonString = '';
        foreach ($polygon as $coord) {
            $polygonString .= $coord[1].' '.$coord[0].' ';
        }
        $polygonString = rtrim($polygonString);

        return $this->getRestaurantsFromIsochrone($polygonString);
    }

    private function getCitiesFromIsochrone(string $polygonString):JsonResponse
    {
        $overpass_url = "https://overpass-api.de/api/interpreter";

        $query = sprintf(
            '[out:json];
            (
            relation["admin_level"="8"]["boundary"="administrative"]["name"~".*"](poly:"' . $polygonString . '");
            );
            out geom;'
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
        ])->get($overpass_url,[
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

    private function getRestaurantsFromIsochrone(string $polygonString):JsonResponse
    {
        $overpass_url = "https://overpass-api.de/api/interpreter";

        $query = sprintf(
            '[out:json];
            (
            node["amenity"="restaurant"](poly:"' . $polygonString . '");
            );
            out geom;'
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
        ])->get($overpass_url,[
            'data' => $query
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $restaurants = [];
            foreach ($data['elements'] ?? [] as $element) {
                dump($element);
                if (isset($element['tags']['amenity']) && $element['tags']['amenity'] == 'restaurant') {
                    $restaurants[] = $element['tags'];
                }
            }
            return response()->json(array_unique($restaurants, SORT_REGULAR));
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}