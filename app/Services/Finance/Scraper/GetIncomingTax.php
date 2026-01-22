<?php 

namespace App\Services\Finance\Scraper;

use App\Services\Tools\CsvQueryService;
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
            $result = $this->getByCsv($parse_code_insee);
            return $this->formatResult($result);
            
        } catch (\Exception $e) {
            Log::error('Error in GetIncomingTax class: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function parseCodeInsee()
    {
        $departement_code = substr($this->code_insee,0,2);
        $city_code = substr($this->code_insee,2);
        return [
            "departement_code" => $departement_code,
            "city_code" => $city_code
        ];
    }

    /**
     * @Deprecated
     * api.data.gouv ne permet pas d'utiliser leur tabular-api pour les données les plus récentes
     * Je garde malgré tout l'appel API si jamais la donnée devient disponible
     */
    private function getByApi(array $parse_code_insee)
    {
        try {
            $base_url = "https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/data/?page_size=20&page=1&DGFiP+-+D%C3%A9partement+des+%C3%A9tudes+statistiques+fiscales__contains=".$parse_code_insee['departement_code']."0"."&Unnamed%3A+1__contains=".$parse_code_insee['city_code']."&Unnamed%3A+3__contains=Total";

            $response = Http::get($base_url);
            $data = $response->json();
            $data['data'][0]['codeinsee'] = $this->code_insee;
            return $data['data'][0];
        } catch (\Exception $e) {
            throw $e;
        }        
    }

    protected function getByCsv($parse_code_insee)
    {
        $csvQueryService = $this->createCsvQueryService();
        $result = $csvQueryService
            ->where('Dép.', $parse_code_insee['departement_code']."0")
            ->where('Commune', $parse_code_insee['city_code'])
            ->where('Revenu fiscal de référence par tranche (en euros)', 'Total')
            ->get()
            ->first();        
        return $result;
    }

    private function formatResult($result)
    {
        if (!$result) {
            throw new \Exception("No data found for code insee: " . $this->code_insee);
        }

        $result['Unnamed: 2'] = $result['Libellé de la commune'];
        $result['Unnamed: 4'] = $result['Nombre de foyers fiscaux'];
        $result['Unnamed: 7'] = $result['Nombre de foyers fiscaux imposés'];
        $result['Unnamed: 9'] = $result['Salaires nombres'];
        $result['Unnamed: 10'] = $result['Salaires montants'];
        $result['Unnamed: 11'] = $result['Retraites nombres'];
        $result['Unnamed: 12'] = $result['Retraites montants'];
        $result['codeinsee'] = $this->code_insee;

        return $result;
    }

    protected function createCsvQueryService()
    {
        return new CsvQueryService('incoming_tax_2023.csv');
    }
}