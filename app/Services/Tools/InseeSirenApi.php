<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;

class InseeSirenApi
{
    public function getCompanies($code_ape, $code_insee)
    {
        $url = "https://api.insee.fr/api-sirene/3.11/siret";
        $query = [
            'q' => 'activitePrincipaleRegistreMetiersEtablissement:'.$code_ape.' AND codeCommuneEtablissement:'.$code_insee,
            'nombre' => 100,
            'champs' => 'activitePrincipaleRegistreMetiersEtablissement,denominationUniteLegale,denominationUsuelleEtablissement,numeroVoieEtablissement,typeVoieEtablissement,libelleVoieEtablissement,libelleCommuneEtablissement,codePostalEtablissement,codeCommuneEtablissement',
        ];
        $token = env('INSEE_SIREN_API_KEY');
        $response = Http::withHeaders([
            'X-INSEE-Api-Key-Integration' => $token,
            'Content-Type' => 'application/json'
        ])->get($url,$query);

        $data = $response->json();
        return $data;
    }
}