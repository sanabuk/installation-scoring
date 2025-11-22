<?php 

namespace App\Services\Finance\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class GetExtraTax
{
    public string $cityname;

    public function __construct(string $cityname)
    {
        $this->cityname = $cityname;
    }

    public function __invoke():JsonResponse
    {
        $response = Http::get('https://data.ofgl.fr/api/records/1.0/search/?rows=40&sort=exer&refine.agregat=Autres+imp%C3%B4ts+et+taxes&refine.exer=2024&refine.com_name='.$this->cityname.'&start=0&fields=exer,com_name,lbudg,type_de_budget,agregat,montant,ptot,euros_par_habitant&dataset=ofgl-base-communes&timezone=Europe%2FBerlin&lang=fr');

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['records']);
        } else {
            return response()->json(['error' => 'Unable to fetch data'], $response->status());
        }
    }
}