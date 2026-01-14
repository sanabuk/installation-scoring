<?php

namespace Tests\Unit\Services\Concurrence\DTO;

use App\Services\Concurrence\DTO\NearbyOrganicVegetableFarmDTO;
use DateTime;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

class NearbyOrganicVegetableFarmDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_created_and_serialized_to_json()
    {
        $dto = new NearbyOrganicVegetableFarmDTO();

        $dto->setName('Ferme Bio');
        $dto->setNameAnnuaire('Ferme Bio Annuaire');
        $dto->setSiret('12345678901234');
        $dto->setDatePremierEngagement(new DateTime('2020-01-01'));
        $dto->setPhone1('0123456789');
        $dto->setPhone2('0987654321');
        $dto->setResponsable('Jean Dupont');
        $dto->setAddress1('1 Rue de la Ferme');
        $dto->setZipcode1('75001');
        $dto->setCity1('Paris');
        $dto->setAddress2('2 Rue de la Ferme');
        $dto->setZipcode2('75002');
        $dto->setCity2('Paris');
        $dto->setUrl('https://ferme-bio.fr');
        $dto->setLon(2.3522);
        $dto->setLat(48.8566);
        $dto->setVenteProsGros(true);
        $dto->setVenteProsDetails(false);
        $dto->setVenteParticuliers(true);
        $dto->setVenteRestoCollective(false);
        $dto->setVenteRestoActivity(true);
        $dto->setHoraires(new stdClass());
        $dto->setDistance(10.5);
        $dto->setCodeInsee('75056');

        $this->assertEquals('Ferme Bio', $dto->getName());
        $this->assertEquals('Ferme Bio Annuaire', $dto->getNameAnnuaire());
        $this->assertEquals('12345678901234', $dto->getSiret());
        $this->assertEquals(new DateTime('2020-01-01'), $dto->getDatePremierEngagement());
        $this->assertEquals('0123456789', $dto->getPhone1());
        $this->assertEquals('0987654321', $dto->getPhone2());
        $this->assertEquals('Jean Dupont', $dto->getResponsable());
        $this->assertEquals('1 Rue de la Ferme', $dto->getAddress1());
        $this->assertEquals('75001', $dto->getZipcode1());
        $this->assertEquals('Paris', $dto->getCity1());
        $this->assertEquals('2 Rue de la Ferme', $dto->getAddress2());
        $this->assertEquals('75002', $dto->getZipcode2());
        $this->assertEquals('Paris', $dto->getCity2());
        $this->assertEquals('https://ferme-bio.fr', $dto->getUrl());
        $this->assertEquals(2.3522, $dto->getLon());
        $this->assertEquals(48.8566, $dto->getLat());
        $this->assertEquals(true, $dto->getVenteProsGros());
        $this->assertEquals(false, $dto->getVenteProsDetails());
        $this->assertEquals(true, $dto->getVenteParticuliers());
        $this->assertEquals(false, $dto->getVenteRestoCollective());
        $this->assertEquals(true, $dto->getVenteRestoActivity());
        $this->assertInstanceOf(stdClass::class, $dto->getHoraires());
        $this->assertEquals(10.5, $dto->getDistance());
        $this->assertEquals('75056', $dto->getCodeInsee());
    }

    #[Test]
    public function it_serializes_to_array_correctly()
    {
        $dto = new NearbyOrganicVegetableFarmDTO();
        $dto->setName('Ferme Bio');
        $dto->setSiret('12345678901234');
        $dto->setHoraires(new stdClass());

        $serialized = $dto->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('siret', $serialized);
        $this->assertArrayHasKey('horaires', $serialized);
        $this->assertEquals('Ferme Bio', $serialized['name']);
        $this->assertEquals('12345678901234', $serialized['siret']);
        $this->assertInstanceOf(stdClass::class, $serialized['horaires']);
    }

    #[Test]
    public function it_handles_null_values_correctly()
    {
        $dto = new NearbyOrganicVegetableFarmDTO();
        $dto->setName('Ferme Bio');
        $dto->setSiret('12345678901234');
        $dto->setHoraires(new stdClass());

        $this->assertNull($dto->getNameAnnuaire());
        $this->assertNull($dto->getDatePremierEngagement());
        $this->assertNull($dto->getPhone1());
        $this->assertNull($dto->getPhone2());
        $this->assertNull($dto->getResponsable());
        $this->assertNull($dto->getAddress1());
        $this->assertNull($dto->getZipcode1());
        $this->assertNull($dto->getCity1());
        $this->assertNull($dto->getAddress2());
        $this->assertNull($dto->getZipcode2());
        $this->assertNull($dto->getCity2());
        $this->assertNull($dto->getUrl());
        $this->assertNull($dto->getLon());
        $this->assertNull($dto->getLat());
        $this->assertNull($dto->getVenteProsGros());
        $this->assertNull($dto->getVenteProsDetails());
        $this->assertNull($dto->getVenteParticuliers());
        $this->assertNull($dto->getVenteRestoCollective());
        $this->assertNull($dto->getVenteRestoActivity());
        $this->assertNull($dto->getDistance());
        $this->assertNull($dto->getCodeInsee());
    }
}
