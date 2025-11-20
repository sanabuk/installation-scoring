<?php 

namespace App\Services\Concurrence\DTO;

class NearbyFarmDTO
{
    protected int $quantity;
    protected int $year;
    protected string $code_insee;
    protected string $name;

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

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}