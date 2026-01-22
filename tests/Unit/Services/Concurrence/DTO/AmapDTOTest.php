<?php

namespace Tests\Unit\Services\Concurrence\DTO;

use App\Services\Concurrence\DTO\AmapDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmapDTOTest extends TestCase
{
    #[Test]
    public function it_can_set_and_get_name()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit le nom
        $dto->setName('AMAP Paris');

        // 3. Vérifie que le nom est correctement récupéré
        $this->assertEquals('AMAP Paris', $dto->getName());
    }

    #[Test]
    public function it_can_set_and_get_city()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit la ville
        $dto->setCity('Paris');

        // 3. Vérifie que la ville est correctement récupérée
        $this->assertEquals('Paris', $dto->getCity());
    }

    #[Test]
    public function it_can_set_and_get_department()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit le département
        $dto->setDepartment('75');

        // 3. Vérifie que le département est correctement récupéré
        $this->assertEquals('75', $dto->getDepartment());
    }

    #[Test]
    public function it_can_set_and_get_websites()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit les sites web
        $dto->setWebsites('https://amap-paris.fr');

        // 3. Vérifie que les sites web sont correctement récupérés
        $this->assertEquals('https://amap-paris.fr', $dto->getWebsites());
    }

    #[Test]
    public function it_can_set_and_get_mail()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit l'email
        $dto->setMail('contact@amap-paris.fr');

        // 3. Vérifie que l'email est correctement récupéré
        $this->assertEquals('contact@amap-paris.fr', $dto->getMail());
    }

    #[Test]
    public function it_can_set_and_get_products()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit les produits
        $dto->setProducts('Légumes, Fruits');

        // 3. Vérifie que les produits sont correctement récupérés
        $this->assertEquals('Légumes, Fruits', $dto->getProducts());
    }

    #[Test]
    public function it_can_set_and_get_infos()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit les infos
        $dto->setInfos('AMAP située dans le 15ème arrondissement');

        // 3. Vérifie que les infos sont correctement récupérées
        $this->assertEquals('AMAP située dans le 15ème arrondissement', $dto->getInfos());
    }

    #[Test]
    public function it_serializes_to_array_correctly()
    {
        // 1. Instancie le DTO
        $dto = new AmapDTO();

        // 2. Définit les propriétés
        $dto->setName('AMAP Paris');
        $dto->setCity('Paris');
        $dto->setDepartment('75');
        $dto->setWebsites('https://amap-paris.fr');
        $dto->setMail('contact@amap-paris.fr');
        $dto->setProducts('Légumes, Fruits');
        $dto->setInfos('AMAP située dans le 15ème arrondissement');

        // 3. Vérifie que la sérialisation est correcte
        $serialized = $dto->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('city', $serialized);
        $this->assertArrayHasKey('department', $serialized);
        $this->assertArrayHasKey('websites', $serialized);
        $this->assertArrayHasKey('mail', $serialized);
        $this->assertArrayHasKey('products', $serialized);
        $this->assertArrayHasKey('infos', $serialized);

        $this->assertEquals('AMAP Paris', $serialized['name']);
        $this->assertEquals('Paris', $serialized['city']);
        $this->assertEquals('75', $serialized['department']);
        $this->assertEquals('https://amap-paris.fr', $serialized['websites']);
        $this->assertEquals('contact@amap-paris.fr', $serialized['mail']);
        $this->assertEquals('Légumes, Fruits', $serialized['products']);
        $this->assertEquals('AMAP située dans le 15ème arrondissement', $serialized['infos']);
    }
}
