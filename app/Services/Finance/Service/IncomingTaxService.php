<?php 

namespace App\Services\Finance\Service;

use App\Services\Finance\Scraper\GetIncomingTax;
use App\Services\Finance\DTO\IncomingTaxDTO;

class IncomingTaxService
{
    public function getIncomingTax(string $code_insee)
    {
        $getIncomingTaxFromApi = new GetIncomingTax($code_insee);
        $rawDatas = $getIncomingTaxFromApi();
        return array_map(
            fn($rawData) => $this->mapToIncomingTaxDTO($rawData), $rawDatas
        );
    }

    private function mapToIncomingTaxDTO($rawData)
    {
        $incomingTaxDTO = new IncomingTaxDTO;
        $incomingTaxDTO->setCodeInsee($rawData['codeinsee']);
        $incomingTaxDTO->setMunicipality($rawData['Unnamed: 2']);
        $incomingTaxDTO->setNumberOfTaxableHouseholds($rawData['Unnamed: 4']);
        $incomingTaxDTO->setNumberOfTaxedHouseholds($rawData['Unnamed: 7']);
        $incomingTaxDTO->setNumberOfHouseholdsTaxedOnSalary($rawData['Unnamed: 9']);
        $incomingTaxDTO->setAmountBySalary($rawData['Unnamed: 10']);
        $incomingTaxDTO->setNumberOfHouseholdsTaxedOnPension($rawData['Unnamed: 11']);
        $incomingTaxDTO->setAmountByPension($rawData['Unnamed: 12']);
        return $incomingTaxDTO;
    }
}