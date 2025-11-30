<?php 

namespace App\Services\Finance\DTO;

class IncomingTaxDTO implements \JsonSerializable
{
    protected string $code_insee;
    protected string $municipality;
    protected int $number_of_taxable_households;
    protected int $number_of_taxed_households;
    protected int $number_of_households_taxed_on_salary;
    protected float $amount_by_salary;
    protected int $number_of_households_taxed_on_pension;
    protected float $amount_by_pension;

    public function jsonSerialize(): array
    {
        return [
            'code_insee' => $this->code_insee,
            'municipality' => $this->municipality,
            'number_of_taxable_households' => $this->number_of_taxable_households,
            'number_of_taxed_households' => $this->number_of_taxed_households,
            'number_of_households_taxed_on_salary' => $this->number_of_households_taxed_on_salary,
            'amount_by_salary' => $this->amount_by_salary,
            'number_of_households_taxed_on_pension' => $this->number_of_households_taxed_on_pension,
            'amount_by_pension' => $this->amount_by_pension,
        ];
    }

    public function getCodeInsee()
    {
        return $this->code_insee;
    }

    public function setCodeInsee($code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function getMunicipality()
    {
        return $this->municipality;
    }

    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;
    }

    public function getNumberOfTaxableHouseholds()
    {
        return $this->number_of_taxable_households;
    }

    public function setNumberOfTaxableHouseholds($number_of_taxable_households)
    {
        $this->number_of_taxable_households = $number_of_taxable_households;
    }

    public function getNumberOfTaxedHouseholds()
    {
        return $this->number_of_taxed_households;
    }

    public function setNumberOfTaxedHouseholds($number_of_taxed_households)
    {
        $this->number_of_taxed_households = $number_of_taxed_households;
    }

    public function getNumberOfHouseholdsTaxedOnSalary()
    {
        return $this->number_of_households_taxed_on_salary;
    }

    public function setNumberOfHouseholdsTaxedOnSalary($number_of_households_taxed_on_salary)
    {
        $this->number_of_households_taxed_on_salary = $number_of_households_taxed_on_salary;
    }

    public function getAmountBySalary()
    {
        return $this->amount_by_salary;
    }

    public function setAmountBySalary($amount_by_salary)
    {
        $this->amount_by_salary = $amount_by_salary;
    }

    public function getNumberOfHouseholdsTaxedOnPension()
    {
        return $this->number_of_households_taxed_on_pension;
    }

    public function setNumberOfHouseholdsTaxedOnPension($number_of_households_taxed_on_pension)
    {
        $this->number_of_households_taxed_on_pension = $number_of_households_taxed_on_pension;
    }

    public function getAmountByPension()
    {
        return $this->amount_by_pension;
    }

    public function setAmountByPension($amount_by_pension)
    {
        $this->amount_by_pension = $amount_by_pension;
    }
}