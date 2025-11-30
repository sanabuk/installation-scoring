<?php 

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\throwException;

class GetNearbyCitiesByDuration
{
    public string $isochrone;

    public function __construct(string $isochrone)
    {
        $this->isochrone = $isochrone;
    }

    public function __invoke():array
    {
        return $this->getCities();
    }

    private function getCities():array
    {
        $cities_from_isochrone = $this->getCitiesFromIsochrone($this->isochrone);
        return $cities_from_isochrone->getOriginalContent();
    }

    /**
     * @throws \Exception
     */
    private function getCitiesFromIsochrone(string $polygonString): JsonResponse
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

        if(!$response->successful()) {
            Log::error('Overpass API request failed with message: ' . $response->status());
            throwException(new \Exception('Failed to retrieve data from Overpass API'));
        }
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
    }    
}