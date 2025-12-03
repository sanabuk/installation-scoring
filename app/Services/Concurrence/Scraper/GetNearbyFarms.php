<?php 

namespace App\Services\Concurrence\Scraper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;

class GetNearbyFarms
{
    public array $code_insee;

    public function __construct(array $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke():JsonResponse
    {
        try {
            $response = Http::get('https://tabular-api.data.gouv.fr/api/resources/75d07d6b-a31d-4ac0-9f1f-7faaa3ecb162/data/', [
                'geocode_commune__in' => implode(',', $this->code_insee)
            ]);
            $data = $response->json();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in GetNearbyFarms class: ' . $e->getMessage());
            throw $e;
        }
    } 
}