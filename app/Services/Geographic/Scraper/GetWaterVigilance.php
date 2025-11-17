<?php 

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class GetWaterVigilance
{
    public float $lon;
    public float $lat;

    public function __construct(float $lon, float $lat)
    {
        $this->lon = $lon;
        $this->lat = $lat;
    }

    public function __invoke():JsonResponse
    {
        $response = Http::get('https://api.vigieau.beta.gouv.fr/api/zones',[
            'lon' => $this->lon,
            'lat' => $this->lat
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}