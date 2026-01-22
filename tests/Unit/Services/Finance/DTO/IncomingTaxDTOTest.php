<?php

namespace Tests\Unit\Services\Finance\DTO;

use App\Services\Finance\DTO\IncomingTaxDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncomingTaxDTOTest extends TestCase
{
    #[Test]
    public function it_can_set_and_get_code_insee()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le code INSEE
        $dto->setCodeInsee('75056');

        // 3. Vérifie que le code INSEE est correctement récupéré
        $this->assertEquals('75056', $dto->getCodeInsee());
    }

    #[Test]
    public function it_can_set_and_get_municipality()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit la municipalité
        $dto->setMunicipality('Paris');

        // 3. Vérifie que la municipalité est correctement récupérée
        $this->assertEquals('Paris', $dto->getMunicipality());
    }

    #[Test]
    public function it_can_set_and_get_number_of_taxable_households()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le nombre de foyers fiscaux
        $dto->setNumberOfTaxableHouseholds(1000);

        // 3. Vérifie que le nombre de foyers fiscaux est correctement récupéré
        $this->assertEquals(1000, $dto->getNumberOfTaxableHouseholds());
    }

    #[Test]
    public function it_can_set_and_get_number_of_taxed_households()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le nombre de foyers imposés
        $dto->setNumberOfTaxedHouseholds(800);

        // 3. Vérifie que le nombre de foyers imposés est correctement récupéré
        $this->assertEquals(800, $dto->getNumberOfTaxedHouseholds());
    }

    #[Test]
    public function it_can_set_and_get_number_of_households_taxed_on_salary()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le nombre de foyers imposés sur salaire
        $dto->setNumberOfHouseholdsTaxedOnSalary(600);

        // 3. Vérifie que le nombre de foyers imposés sur salaire est correctement récupéré
        $this->assertEquals(600, $dto->getNumberOfHouseholdsTaxedOnSalary());
    }

    #[Test]
    public function it_can_set_and_get_amount_by_salary()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le montant par salaire
        $dto->setAmountBySalary(1500000.50);

        // 3. Vérifie que le montant par salaire est correctement récupéré
        $this->assertEquals(1500000.50, $dto->getAmountBySalary());
    }

    #[Test]
    public function it_can_set_and_get_number_of_households_taxed_on_pension()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le nombre de foyers imposés sur pension
        $dto->setNumberOfHouseholdsTaxedOnPension(200);

        // 3. Vérifie que le nombre de foyers imposés sur pension est correctement récupéré
        $this->assertEquals(200, $dto->getNumberOfHouseholdsTaxedOnPension());
    }

    #[Test]
    public function it_can_set_and_get_amount_by_pension()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit le montant par pension
        $dto->setAmountByPension(500000.75);

        // 3. Vérifie que le montant par pension est correctement récupéré
        $this->assertEquals(500000.75, $dto->getAmountByPension());
    }

    #[Test]
    public function it_serializes_to_array_correctly()
    {
        // 1. Instancie le DTO
        $dto = new IncomingTaxDTO();

        // 2. Définit les propriétés
        $dto->setCodeInsee('75056');
        $dto->setMunicipality('Paris');
        $dto->setNumberOfTaxableHouseholds(1000);
        $dto->setNumberOfTaxedHouseholds(800);
        $dto->setNumberOfHouseholdsTaxedOnSalary(600);
        $dto->setAmountBySalary(1500000.50);
        $dto->setNumberOfHouseholdsTaxedOnPension(200);
        $dto->setAmountByPension(500000.75);

        // 3. Vérifie que la sérialisation est correcte
        $serialized = $dto->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('code_insee', $serialized);
        $this->assertArrayHasKey('municipality', $serialized);
        $this->assertArrayHasKey('number_of_taxable_households', $serialized);
        $this->assertArrayHasKey('number_of_taxed_households', $serialized);
        $this->assertArrayHasKey('number_of_households_taxed_on_salary', $serialized);
        $this->assertArrayHasKey('amount_by_salary', $serialized);
        $this->assertArrayHasKey('number_of_households_taxed_on_pension', $serialized);
        $this->assertArrayHasKey('amount_by_pension', $serialized);

        $this->assertEquals('75056', $serialized['code_insee']);
        $this->assertEquals('Paris', $serialized['municipality']);
        $this->assertEquals(1000, $serialized['number_of_taxable_households']);
        $this->assertEquals(800, $serialized['number_of_taxed_households']);
        $this->assertEquals(600, $serialized['number_of_households_taxed_on_salary']);
        $this->assertEquals(1500000.50, $serialized['amount_by_salary']);
        $this->assertEquals(200, $serialized['number_of_households_taxed_on_pension']);
        $this->assertEquals(500000.75, $serialized['amount_by_pension']);
    }
}
