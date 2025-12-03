<?php 

namespace App\Services\Finance\Scraper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetIncomingTax
{
    public string $code_insee;

    public function __construct(string $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke()
    {
        try {
            $parse_code_insee = $this->parseCodeInsee();
            $base_url = "https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/data/?page_size=20&page=1&DGFiP+-+D%C3%A9partement+des+%C3%A9tudes+statistiques+fiscales__contains=".$parse_code_insee['departement_code']."0"."&Unnamed%3A+1__contains=".$parse_code_insee['city_code']."&Unnamed%3A+3__contains=Total";

            $response = Http::get($base_url);
            $data = $response->json();
            $data['data'][0]['codeinsee'] = $this->code_insee;
            return $data['data'][0];
        } catch (\Exception $e) {
            Log::error('Error in GetIncomingTax class: ' . $e->getMessage());
            throw $e;
        }
    }

    private function parseCodeInsee()
    {
        $departement_code = substr($this->code_insee,0,2);
        $city_code = substr($this->code_insee,2);
        return [
            "departement_code" => $departement_code,
            "city_code" => $city_code
        ];
    }
}