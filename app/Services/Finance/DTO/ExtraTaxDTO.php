<?php 

namespace App\Services\Finance\DTO;

class ExtraTaxDTO
{
    protected float $euros_per_resident;
    protected string $city;

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
}