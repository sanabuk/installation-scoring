<?php 

namespace App\Services\Finance\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class GetExtraTax
{
    public array $codes_insee;

    public function __construct(array $codes_insee)
    {
        $this->codes_insee = $codes_insee;
    }

    public function __invoke():JsonResponse
    {
        $response = Http::get('https://data.ofgl.fr/api/explore/v2.1/catalog/datasets/ofgl-base-communes-consolidee/records?where=insee = \''.implode("' OR '", $this->codes_insee).'\'&limit=30&refine=exer:"2024"&refine=agregat:"Autres impôts et taxes"');

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['results']);
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}