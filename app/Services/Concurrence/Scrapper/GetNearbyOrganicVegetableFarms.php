<?php 

namespace App\Services\Concurrence\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http; 

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
        $base_url = 'https://annuaire-back.agencebio.org/operateurs';
        $payload = [
            "typesProfessionnels" => "ferme",
            "activities" => 18,
            "dist" => $this->radius_km,
            "lng" => $this->lon,
            "lat" => $this->lat
        ];

        $response = Http::get($base_url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data;
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}