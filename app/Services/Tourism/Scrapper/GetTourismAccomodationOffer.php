<?php 

namespace App\Services\Tourism\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetTourismAccomodationOffer
{
    public string $code_insee;

    public function __construct(string $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke()
    {
        $response = Http::get('https://api.insee.fr/melodi/data/DS_TOUR_CAP?maxResult=200&GEO=2025-COM-'.$this->code_insee);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}