<?php

namespace App\Services\Concurrence\DTO;

class AmapDTO implements \JsonSerializable
{
    protected string $name;
    protected string $city;
    protected string $department;
    protected string $websites;
    protected string $mail;
    protected string $products;
    protected string $infos;

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'city' => $this->city,
            'department' => $this->department,
            'websites' => $this->websites,
            'mail' => $this->mail,
            'products' => $this->products,
            'infos' => $this->infos,
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
    }

    public function getWebsites()
    {
        return $this->websites;
    }

    public function setWebsites($websites)
    {
        $this->websites = $websites;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products)
    {
        $this->products = $products;
    }

    public function getInfos()
    {
        return $this->infos;
    }

    public function setInfos($infos)
    {
        $this->infos = $infos;
    }
}