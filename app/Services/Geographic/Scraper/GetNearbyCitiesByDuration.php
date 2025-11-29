<?php 

namespace App\Services\Geographic\Scraper;

use App\Services\Tools\GetIsochroneByDuration;
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

    public function __invoke():array
    {
        return $this->getCities();
    }

    private function getCities():array
    {
        $getIsochroneService = new GetIsochroneByDuration($this->lat, $this->lon, $this->duration);
        $polygon = $getIsochroneService->getIsochrone();

        list($first_polygon, $second_polygon) = $getIsochroneService->splitIsochrone($polygon);

        $cities_from_first_polygon = $this->getCitiesFromIsochrone($first_polygon);
        $cities_from_second_polygon = $this->getCitiesFromIsochrone($second_polygon);

        $cities = array_merge($cities_from_first_polygon->getOriginalContent(), $cities_from_second_polygon->getOriginalContent());
        //dump($cities);
        $cities_unique = array_values(array_unique($cities,SORT_REGULAR));
        //dump($cities_unique);
        return $cities_unique;
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
                        'code_postal' => $element['tags']['postal_code'],
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