<?php 

namespace App\Services\Finance\Scrapper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetIncomingTax
{
    public string $code_insee;

    public function __construct(string $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function __invoke()
    {
        $parse_code_insee = $this->parseCodeInsee();
        $base_url = "https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/data/?page_size=20&page=1&DGFiP+-+D%C3%A9partement+des+%C3%A9tudes+statistiques+fiscales__contains=".$parse_code_insee['departement_code']."0"."&Unnamed%3A+1__contains=".$parse_code_insee['city_code'];

        $response = Http::get($base_url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'];
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
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