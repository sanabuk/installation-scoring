<?php

namespace Tests\Unit\Services\Finance\Service;

use App\Services\Finance\DTO\IncomingTaxDTO;
use App\Services\Finance\Service\IncomingTaxService;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncomingTaxServiceTest extends TestCase
{
    #[Test]
    public function it_maps_raw_data_to_incoming_tax_dto_correctly()
    {
        // 1. Instancie le service
        $service = new IncomingTaxService();

        // 2. Données brutes simulées
        $rawData = [
            'codeinsee' => '75056',
            'Unnamed: 2' => 'Paris',
            'Unnamed: 4' => 1000,
            'Unnamed: 7' => 800,
            'Unnamed: 9' => 600,
            'Unnamed: 10' => 1500000.50,
            'Unnamed: 11' => 200,
            'Unnamed: 12' => 500000.75,
        ];

        // 3. Appelle la méthode privée mapToIncomingTaxDTO
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapToIncomingTaxDTO');
        $method->setAccessible(true);
        $result = $method->invoke($service, $rawData);

        // 4. Vérifie que le résultat est un IncomingTaxDTO
        $this->assertInstanceOf(IncomingTaxDTO::class, $result);
        $this->assertEquals('75056', $result->getCodeInsee());
        $this->assertEquals('Paris', $result->getMunicipality());
        $this->assertEquals(1000, $result->getNumberOfTaxableHouseholds());
        $this->assertEquals(800, $result->getNumberOfTaxedHouseholds());
        $this->assertEquals(600, $result->getNumberOfHouseholdsTaxedOnSalary());
        $this->assertEquals(1500000.50, $result->getAmountBySalary());
        $this->assertEquals(200, $result->getNumberOfHouseholdsTaxedOnPension());
        $this->assertEquals(500000.75, $result->getAmountByPension());
    }

    #[Test]
    public function it_returns_incoming_tax_dto()
    {
        // 1. Mock du callable GetIncomingTax
        $scraperMock = Mockery::mock(\App\Services\Finance\Scraper\GetIncomingTax::class);
        $scraperMock->shouldReceive('__invoke')
            ->once()
            ->andReturn([
                'codeinsee' => '75056',
                'Unnamed: 2' => 'Paris',
                'Unnamed: 4' => 1000,
                'Unnamed: 7' => 800,
                'Unnamed: 9' => 600,
                'Unnamed: 10' => 1500000.50,
                'Unnamed: 11' => 200,
                'Unnamed: 12' => 500000.75,
            ]);

        // 2. Partial mock du service
        $service = Mockery::mock(IncomingTaxService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // 3. On remplace uniquement la factory
        $service->shouldReceive('createGetIncomingTax')
            ->once()
            ->with('75056')
            ->andReturn($scraperMock);

        // 4. Appel réel de la méthode testée
        $result = $service->getIncomingTax('75056');

        // 5. Assertions
        $this->assertInstanceOf(IncomingTaxDTO::class, $result);
        $this->assertEquals('Paris', $result->getMunicipality());
    }

    #[Test]
    public function it_logs_errors_when_get_incoming_tax_fails()
    {
        $scraperMock = Mockery::mock(\App\Services\Finance\Scraper\GetIncomingTax::class);
        $scraperMock->shouldReceive('__invoke')
            ->once()
            ->andThrow(new \Exception('API request failed'));

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(fn($msg) => str_contains($msg, 'API request failed')));

        $service = Mockery::mock(IncomingTaxService::class)->makePartial()->shouldAllowMockingProtectedMethods();;

        $service->shouldReceive('createGetIncomingTax')
            ->once()
            ->andReturn($scraperMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API request failed');

        $service->getIncomingTax('75056');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
