<?php 

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\throwException;

class GetNearbyCitiesByDuration
{
    public string $isochrone;
    public int $interval;

    public function __construct(string $isochrone, int $interval)
    {
        $this->isochrone = $isochrone;
        $this->interval = $interval;
    }

    public function __invoke():array
    {
        try {
            return $this->getCities();
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    private function getCities():array
    {
        try {
            $cities_from_isochrone = $this->getCitiesFromIsochrone($this->isochrone);
            return $cities_from_isochrone->getOriginalContent();
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    /**
     * @throws \Exception
     */
    private function getCitiesFromIsochrone(string $polygonString): JsonResponse
    {
        try {
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
            
            $data = $response->json();
            $communes = [];
            foreach ($data['elements'] ?? [] as $element) {
                if (isset($element['tags']['name']) && isset($element['tags']['ref:INSEE']) && isset($element['tags']['population'])) {
                    $communes[] = [
                        'name' => $element['tags']['name'],
                        'code_insee' => $element['tags']['ref:INSEE'],
                        'code_postal' => $element['tags']['postal_code'],
                        'population' => $element['tags']['population'],
                        'limit_duration' => $this->interval
                    ];
                }
            }
            return response()->json(array_unique($communes, SORT_REGULAR));
        } catch (\Exception $e) {
            Log::error('Error in GetNearbyCitiesByDuration class: ' . $e->getMessage());
            throw $e;
        }

    }    
}