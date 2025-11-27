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

    public function __invoke():array
    {
        return $this->getIsochrone();
    }

    private function getIsochrone():array
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

        list($first_polygon, $second_polygon) = $this->splitIsochrone($polygon);

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

    private function splitIsochrone(array $isochrone):array
    {
        $isochrone_length = count($isochrone);
        $isochrone_split_key = floor($isochrone_length/2);
        $first_polygon = $isochrone[0];

        $polygon_first_string = '';
        for ($i=0; $i < $isochrone_split_key; $i++) { 
            $polygon_first_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_first_string .= $first_polygon[1].' '. $first_polygon[0];
        $polygon_first_string = rtrim($polygon_first_string);

        $polygon_second_string = '';
        for ($i=$isochrone_split_key; $i < $isochrone_length; $i++) { 
            $polygon_second_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_second_string .= $isochrone[$isochrone_split_key][1].' '. $isochrone[$isochrone_split_key][0];

        return [$polygon_first_string, $polygon_second_string];
    }
}