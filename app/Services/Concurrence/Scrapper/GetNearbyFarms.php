<?php 

namespace App\Services\Concurrence\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http; 

class GetNearbyFarms
{
    public array $code_insee;

    public function __construct(array $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke():JsonResponse
    {
        $response = Http::get('https://tabular-api.data.gouv.fr/api/resources/75d07d6b-a31d-4ac0-9f1f-7faaa3ecb162/data/?geocode_commune__in='.implode(',', $this->code_insee));

        if ($response->successful()) {
            $data = $response->json();
            return $data;
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    } 
}