<?php 

namespace App\Services\Finance\DTO;

class ExtraTaxDTO
{
    protected float $euros_per_resident;
    protected string $city;
    protected string $code_insee;

    public function getEurosPerResident()
    {
        return $this->euros_per_resident;
    }

    public function setEurosPerResident($euros_per_resident)
    {
        $this->euros_per_resident = $euros_per_resident;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCodeInsee()
    {
        return $this->code_insee;
    }

    public function setCodeInsee($code_insee)
    {
        $this->code_insee = $code_insee;
    }
}