<?php

namespace Tests\Unit\Services\Concurrence\Service;

use App\Services\Concurrence\DTO\AmapDTO;
use App\Services\Concurrence\Scraper\GetAmap;
use App\Services\Concurrence\Service\AmapService;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmapServiceTest extends TestCase
{
    #[Test]
    public function it_returns_an_array_of_amap_dto()
    {
        // 1. Mock la réponse de GetAmap
        $mockGetAmap = Mockery::mock(GetAmap::class);
        $mockGetAmap->shouldReceive('__invoke')
            ->once()
            ->andReturn([
                [
                    'Nom' => 'AMAP Paris',
                    'Ville' => 'Paris',
                    'Departement' => '75',
                    'Websites' => 'https://amap-paris.fr',
                    'Mail' => 'contact@amap-paris.fr',
                    'Produits' => 'Légumes, Fruits',
                    'Infos' => 'AMAP située dans le 15ème arrondissement',
                ],
                [
                    'Nom' => 'AMAP Lyon',
                    'Ville' => 'Lyon',
                    'Departement' => '69',
                    'Websites' => 'https://amap-lyon.fr',
                    'Mail' => 'contact@amap-lyon.fr',
                    'Produits' => 'Légumes, Œufs',
                    'Infos' => 'AMAP située près de la Part-Dieu',
                ]
            ]);

        // 2. Instancie le service et crée un mock partiel
        $service = Mockery::mock(AmapService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode protégée createGetAmap pour retourner le mock de GetAmap
        $service->shouldReceive('createGetAmap')
            ->with('75', 'Paris')
            ->andReturn($mockGetAmap);

        // 4. Appelle la méthode à tester
        $result = $service->getAmap('75', 'Paris');

        // 5. Vérifie que le résultat est un tableau de AmapDTO
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(AmapDTO::class, $result[0]);
        $this->assertInstanceOf(AmapDTO::class, $result[1]);
        $this->assertEquals('AMAP Paris', $result[0]->getName());
        $this->assertEquals('AMAP Lyon', $result[1]->getName());
    }

    #[Test]
    public function it_maps_raw_data_to_amap_dto_correctly()
    {
        // 1. Instancie le service
        $service = new AmapService();

        // 2. Données brutes simulées
        $rawDatas = [
            [
                'Nom' => 'AMAP Paris',
                'Ville' => 'Paris',
                'Departement' => '75',
                'Websites' => 'https://amap-paris.fr',
                'Mail' => 'contact@amap-paris.fr',
                'Produits' => 'Légumes, Fruits',
                'Infos' => 'AMAP située dans le 15ème arrondissement',
            ]
        ];

        // 3. Appelle la méthode privée mapToAmapDTO
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapToAmapDTO');
        $method->setAccessible(true);
        $result = $method->invoke($service, $rawDatas);

        // 4. Vérifie que le résultat est un tableau de AmapDTO
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(AmapDTO::class, $result[0]);
        $this->assertEquals('AMAP Paris', $result[0]->getName());
        $this->assertEquals('Paris', $result[0]->getCity());
        $this->assertEquals('75', $result[0]->getDepartment());
        $this->assertEquals('https://amap-paris.fr', $result[0]->getWebsites());
        $this->assertEquals('contact@amap-paris.fr', $result[0]->getMail());
        $this->assertEquals('Légumes, Fruits', $result[0]->getProducts());
        $this->assertEquals('AMAP située dans le 15ème arrondissement', $result[0]->getInfos());
    }

    #[Test]
    public function it_logs_errors_when_get_amap_fails()
    {
        // 1. Mock une exception levée par GetAmap
        $mockGetAmap = Mockery::mock(GetAmap::class);
        $mockGetAmap->shouldReceive('__invoke')
            ->once()
            ->andThrow(new \Exception('API request failed'));

        // 2. Instancie le service et crée un mock partiel
        $service = Mockery::mock(AmapService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode protégée createGetAmap pour retourner le mock de GetAmap
        $service->shouldReceive('createGetAmap')
            ->with('75', 'Paris')
            ->andReturn($mockGetAmap);

        // 4. Espionne le Log pour vérifier que l'erreur est loggée
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Error in AmapService class');
            });

        // 5. Vérifie qu'une exception est levée
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API request failed');

        // 6. Appelle la méthode à tester
        $service->getAmap('75', 'Paris');
    }

    #[Test]
    public function it_logs_errors_when_mapping_fails()
    {
        // 1. Mock la réponse de GetAmap
        $mockGetAmap = Mockery::mock(GetAmap::class);
        $mockGetAmap->shouldReceive('__invoke')
            ->once()
            ->andReturn([['Invalid' => 'Data']]);

        // 2. Instancie le service et crée un mock partiel
        $service = Mockery::mock(AmapService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode protégée createGetAmap pour retourner le mock de GetAmap
        $service->shouldReceive('createGetAmap')
            ->with('75', 'Paris')
            ->andReturn($mockGetAmap);

        // 4. Espionne le Log pour vérifier que l'erreur est loggée
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Error mapping to AmapDTO');
            });

        // 5. Vérifie qu'une exception est levée
        $this->expectException(\Exception::class);

        // 6. Appelle la méthode à tester
        $service->getAmap('75', 'Paris');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
