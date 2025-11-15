<?php 

namespace App\Services\Geographic\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetPopulation
{
    public function __invoke(string $code_insee):int|JsonResponse
    {
        /**
         * Cette API permet de récupérer pour une commune donnée
         * - codeDepartement
         * - siren
         * - codeEpci
         * - codeRegion
         * - codePostaux[]
         * - population
         * Pour le moment seule la donnée a un intérêt
         */
        $response = Http::get('https://geo.api.gouv.fr/communes/'.$code_insee);

        if ($response->successful()) {
            $data = $response->json();
            return $data['population'];
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}