<?php 

namespace App\Services\Finance\Service;

use App\Services\Finance\Scraper\GetIncomingTax;
use App\Services\Finance\DTO\IncomingTaxDTO;
use Illuminate\Support\Facades\Log;

class IncomingTaxService
{
    public function getIncomingTax(string $code_insee)
    {
        try {
            $getIncomingTaxFromApi = $this->createGetIncomingTax($code_insee);
            $rawDatas = $getIncomingTaxFromApi();
            return $this->mapToIncomingTaxDTO($rawDatas);
        } catch (\Exception $e) {
            Log::error('Error in IncomingTaxService class: ' . $e->getMessage());
            throw $e;
        }
        
    }

    protected function createGetIncomingTax(string $code_insee): GetIncomingTax
    {
        return new GetIncomingTax($code_insee);
    }

    protected function mapToIncomingTaxDTO($rawData)
    {
        try {
            $incomingTaxDTO = new IncomingTaxDTO;
            $incomingTaxDTO->setCodeInsee($rawData['codeinsee']);
            $incomingTaxDTO->setMunicipality($rawData['Unnamed: 2']);
            $incomingTaxDTO->setNumberOfTaxableHouseholds((int)$rawData['Unnamed: 4']);
            $incomingTaxDTO->setNumberOfTaxedHouseholds((int)$rawData['Unnamed: 7']);
            $incomingTaxDTO->setNumberOfHouseholdsTaxedOnSalary((int)$rawData['Unnamed: 9']);
            $incomingTaxDTO->setAmountBySalary((float)$rawData['Unnamed: 10']);
            $incomingTaxDTO->setNumberOfHouseholdsTaxedOnPension((int)$rawData['Unnamed: 11']);
            $incomingTaxDTO->setAmountByPension((float)$rawData['Unnamed: 12']);
            return $incomingTaxDTO;
        } catch (\Exception $e) {
            Log::error('Error mapping to IncomingTaxDTO: ' . $e->getMessage());
            throw $e;
        }
        
    }
}