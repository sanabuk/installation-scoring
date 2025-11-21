<?php 

namespace App\Services\Concurrence\DTO;

use DateTime;
use stdClass;

class NearbyOrganicVegetableFarmDTO
{
    protected string $name;
    protected ?string $name_annuaire;
    protected string $siret;
    protected DateTime|null $date_premier_engagement;
    protected ?string $phone1;
    protected ?string $phone2;
    protected ?string $responsable;
    protected ?string $address1;
    protected ?string $zipcode1;
    protected ?string $city1;
    protected ?string $address2;
    protected ?string $zipcode2;
    protected ?string $city2;
    protected ?string $url;
    protected ?float $lon;
    protected ?float $lat;
    protected ?bool $vente_pros_gros;
    protected ?bool $vente_pros_details;
    protected ?bool $vente_particuliers;
    protected ?bool $vente_resto_collective;
    protected ?bool $vente_resto_activity;
    protected stdClass $horaires;
    protected float $distance;

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getNameAnnuaire()
    {
        return $this->name_annuaire;
    }

    public function setNameAnnuaire(?string $name_annuaire)
    {
        $this->name_annuaire = $name_annuaire;
    }

    public function getSiret()
    {
        return $this->siret;
    }

    public function setSiret(string $siret)
    {
        $this->siret = $siret;
    }

    public function getDatePremierEngagement()
    {
        return $this->date_premier_engagement;
    }

    public function setDatePremierEngagement(?DateTime $date_premier_engagement)
    {
        $this->date_premier_engagement = $date_premier_engagement;
    }

    public function getPhone1()
    {
        return $this->phone1;
    }

    public function setPhone1(?string $phone)
    {
        $this->phone1 = $phone;
    }

    public function getPhone2()
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone)
    {
        $this->phone2 = $phone;
    }

    public function getResponsable()
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable)
    {
        $this->responsable = $responsable;
    }

    public function getAddress1()
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1)
    {
        $this->address1 = $address1;
    }

    public function getZipcode1()
    {
        return $this->zipcode1;
    }

    public function setZipcode1(?string $zipcode1)
    {
        $this->zipcode1 = $zipcode1;
    }

    public function getCity1()
    {
        return $this->city1;
    }

    public function setCity1(?string $city1)
    {
        $this->city1 = $city1;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2)
    {
        $this->address2 = $address2;
    }

    public function getZipcode2()
    {
        return $this->zipcode2;
    }

    public function setZipcode2(?string $zipcode2)
    {
        $this->zipcode2 = $zipcode2;
    }

    public function getCity2()
    {
        return $this->city2;
    }

    public function setCity2(?string $city2)
    {
        $this->city2 = $city2;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(?string $url)
    {
        $this->url = $url;
    }

    public function getLon()
    {
        return $this->lon;
    }

    public function setLon(?float $lon)
    {
        $this->lon = $lon;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function setLat(?float $lat)
    {
        $this->lat = $lat;
    }

    public function getVenteProsGros()
    {
        return $this->vente_pros_gros;
    }

    public function setVenteProsGros(?bool $vente_pros_gros)
    {
        $this->vente_pros_gros = $vente_pros_gros;
    }

    public function getVenteProsDetails()
    {
        return $this->vente_pros_details;
    }

    public function setVenteProsDetails(?bool $vente_pros_details)
    {
        $this->vente_pros_details = $vente_pros_details;
    }

    public function getVenteParticuliers()
    {
        return $this->vente_particuliers;
    }

    public function setVenteParticuliers(?bool $vente_particuliers)
    {
        $this->vente_particuliers = $vente_particuliers;
    }

    public function getVenteRestoCollective()
    {
        return $this->vente_resto_collective;
    }

    public function setVenteRestoCollective(?bool $vente_resto_collective)
    {
        $this->vente_resto_collective = $vente_resto_collective;
    }

    public function getVenteRestoActivity()
    {
        return $this->vente_resto_activity;
    }

    public function setVenteRestoActivity(?bool $vente_resto_activity)
    {
        $this->vente_resto_activity = $vente_resto_activity;
    }

    public function getHoraires()
    {
        return $this->horaires;
    }

    public function setHoraires(stdClass $horaires)
    {
        $this->horaires = $horaires;
    }

    public function getDistance()
    {
        return $this->distance;
    }

    public function setDistance(?float $distance)
    {
        $this->distance = $distance; 
    }

}