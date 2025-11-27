<?php 

namespace App\Services\Finance\Service;

use App\Services\Finance\Scraper\GetExtraTax;
use App\Services\Finance\DTO\ExtraTaxDTO;

class ExtraTaxService
{
    public function getExtraTax(array $codes_insee)
    {
        $getExtraTaxFromApi = new GetExtraTax($codes_insee);
        $rawDatas = $getExtraTaxFromApi();
        return array_map(
            fn($rawData) => $this->mapToExtraTaxDTO($rawData), json_decode($rawDatas->getContent())
        );

    }

    private function mapToExtraTaxDTO($rawData)
    {
        $extraTaxDTO = new ExtraTaxDTO();
        $extraTaxDTO->setCity($rawData->com_name);
        $extraTaxDTO->setEurosPerResident($rawData->euros_par_habitant);
        $extraTaxDTO->setCodeInsee($rawData->insee);
        return $extraTaxDTO;
    }
}