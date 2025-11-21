<?php 

namespace App\Services\Concurrence\DTO;

class NearbyFarmDTO
{
    protected int $quantity;
    protected int $year;
    protected string $code_insee;
    protected string $municipality_name;

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function getCodeInsee()
    {
        return $this->code_insee;
    }

    public function setCodeInsee(string $code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear(int $year)
    {
        $this->year = $year;
    }

    public function getMunicipalityName()
    {
        return $this->municipality_name;
    }

    public function setMunicipalityName(string $name)
    {
        $this->municipality_name = $name;
    }
}