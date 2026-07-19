<?php

namespace App\Services\Tourism\DTO;

class MarketplaceDTO implements \JsonSerializable
{
    protected ?string $city;
    protected ?string $code_postal;
    protected ?string $website;
    protected ?string $name;
    protected ?string $horaires;

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'city' => $this->city,
            'code_postal' => $this->code_postal,
            'website' => $this->website,
            'horaires' => $this->horaires,
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
        return $this->code_postal;
    }

    public function setPostcode(?string $code_postal): void
    {
        $this->code_postal = $code_postal;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getHoraires(): ?string
    {
        return $this->horaires;
    }

    public function setHoraires(?string $horaires): void
    {
        $this->horaires = $horaires;
    }
}