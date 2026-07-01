<?php 

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\throwException;

class GetNearbyCitiesByDuration
{
    public string $type;
    public array $isochrone;
    public int $interval;

    public function __construct(string $type, array $isochrone, int $interval)
    {
        $this->type = $type;
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
            $cities_from_isochrone = $this->getCitiesFromIsochrone();
            return $cities_from_isochrone;
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    /**
     * @throws \Exception
     */
    private function getCitiesFromIsochrone(): array
    {
        try {
            $apicarto_api_url = 'https://apicarto.ign.fr/api/limites-administratives/commune';
            $geom = [
                'type' => $this->type,
                'coordinates' => [$this->isochrone]
            ];
            $response = Http::get($apicarto_api_url, [
                'geom' => json_encode($geom),
            ]);

            foreach($response->json()['features'] as $feature){
                $nearbyMunicipalities[] = [
                    'name' => $feature['properties']['nom_com'],
                    'code_insee' => $feature['properties']['insee_com'],
                    'code_postal' => $feature['properties']['code_postal'],
                    'population' => $feature['properties']['population'],
                    'limit_duration' => $this->interval
                ];
            }

            return $nearbyMunicipalities ?? [];

        } catch (\Exception $e) {
            Log::error('Error in GetNearbyCitiesByDuration class: ' . $e->getMessage());
            throw $e;
        }

    }    
}