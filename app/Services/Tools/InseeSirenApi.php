<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;

class InseeSirenApi
{
    public function getRestaurants($code_insee)
    {
        $url = "https://api.insee.fr/api-sirene/3.11/siret";
        $query = [
            'q' => 'activitePrincipaleRegistreMetiersEtablissement:5610C* AND codeCommuneEtablissement:37261',
            'nombre' => 100,
            'champs' => 'activitePrincipaleRegistreMetiersEtablissement,denominationUniteLegale,denominationUsuelleEtablissement,numeroVoieEtablissement,typeVoieEtablissement,libelleVoieEtablissement,libelleCommuneEtablissement,codePostalEtablissement,codeCommuneEtablissement',
        ];
        $token = env('INSEE_SIREN_API_KEY');
        $response = Http::withHeaders([
            'X-INSEE-Api-Key-Integration' => $token,
            'Content-Type' => 'application/json'
        ])->get($url,$query);

        $data = $response->json();
        dump($data);
    }
}