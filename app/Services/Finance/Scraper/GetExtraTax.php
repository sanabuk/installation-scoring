<?php 

namespace App\Services\Finance\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetExtraTax
{
    public array $codes_insee;

    public function __construct(array $codes_insee)
    {
        $this->codes_insee = $codes_insee;
    }

    public function __invoke():JsonResponse
    {
        try {
            $response = Http::get('https://data.ofgl.fr/api/explore/v2.1/catalog/datasets/ofgl-base-communes-consolidee/records', [
                'where' => "insee = '".implode("' OR '", $this->codes_insee)."'",
                'limit' => 50,
                'refine' => 'exer:"2024"',
                'refine' => 'agregat:"Autres impôts et taxes"'
            ]);
            $data = $response->json();
            return response()->json($data['results']);
        } catch (\Exception $e) {
            Log::error('Error in GetExtraTax class: ' . $e->getMessage());
            throw $e;
        }
    }
}