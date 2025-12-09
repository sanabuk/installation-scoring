<?php

namespace App\Services\Tourism\DTO;

class MarketplaceDTO implements \JsonSerializable
{
    protected string $name;
    protected ?string $address;
    protected ?string $city;
    protected ?string $postcode;
    protected ?string $website;
    protected ?string $phone;
    protected ?float $lat;
    protected ?float $lon;
    protected ?string $code_insee;

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'website' => $this->website,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'code_insee' => $this->code_insee,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }  
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): void
    {
        $this->lon = $lon;
    }

    public function getCodeInsee(): ?string
    {
        return $this->code_insee;
    }

    public function setCodeInsee(?string $code_insee): void
    {
        $this->code_insee = $code_insee;
    }
}