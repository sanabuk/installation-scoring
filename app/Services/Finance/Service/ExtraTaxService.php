<?php 

namespace App\Services\Finance\Service;

use App\Services\Finance\Scraper\GetExtraTax;
use App\Services\Finance\DTO\ExtraTaxDTO;

class ExtraTaxService
{
    public function getExtraTax(string $city)
    {
        $getExtraTaxFromApi = new GetExtraTax($city);
        $rawDatas = $getExtraTaxFromApi();
        return array_map(
            fn($rawData) => $this->mapToExtraTaxDTO($rawData), json_decode($rawDatas->getContent())
        );

    }

    private function mapToExtraTaxDTO($rawData)
    {
        $extraTaxDTO = new ExtraTaxDTO();
        $extraTaxDTO->setCity($rawData->fields->com_name);
        $extraTaxDTO->setEurosPerResident($rawData->fields->euros_par_habitant);
        return $extraTaxDTO;
    }
}