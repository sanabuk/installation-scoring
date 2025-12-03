<?php 

namespace App\Services\Concurrence\Scraper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetNearbyOrganicVegetableFarms
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
        try {
            $base_url = 'https://annuaire-back.agencebio.org/operateurs';
            $payload = [
                "userPage" => 1,
                "activities" => 18,
                "rand" => 865,
                "page" => 1,
                "profils" => "Ferme",
                "typesProfessionnels" => "ferme",
                "sortBy" => "distance",
                "dist" => $this->radius_km,
                "lat" => $this->lat,
                "lng" => $this->lon,
                "nb" => 200          
            ];

            $response = Http::get($base_url, $payload);
            $data = $response->json();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in GetNearbyOrganicVegetableFarms class: ' . $e->getMessage());
            throw $e;
        }
    }
}