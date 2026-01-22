<?php

namespace Tests\Unit\Services\Finance\Scraper;

use App\Services\Finance\Scraper\GetIncomingTax;
use App\Services\Tools\CsvQueryService;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetIncomingTaxTest extends TestCase
{
    #[Test]
    public function it_parses_code_insee_correctly()
    {
        // 1. Instancie la classe avec un code INSEE
        $getIncomingTax = new GetIncomingTax('75056');

        // 2. Utilise la réflexion pour accéder à la méthode protégée parseCodeInsee
        $reflection = new \ReflectionClass($getIncomingTax);
        $method = $reflection->getMethod('parseCodeInsee');
        $method->setAccessible(true);

        // 3. Appelle la méthode parseCodeInsee
        $result = $method->invoke($getIncomingTax);

        // 4. Vérifie que le parsing est correct
        $this->assertIsArray($result);
        $this->assertArrayHasKey('departement_code', $result);
        $this->assertArrayHasKey('city_code', $result);
        $this->assertEquals('75', $result['departement_code']);
        $this->assertEquals('056', $result['city_code']);
    }

    #[Test]
    public function it_returns_formatted_data_from_csv()
    {
        // 1. Instancie la classe avec un code INSEE
        $getIncomingTax = new GetIncomingTax('75056');

        // 2. Utilise un mock partiel pour remplacer la méthode __invoke
        $servicePartialMock = Mockery::mock($getIncomingTax)->makePartial();
        $servicePartialMock->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode __invoke pour retourner directement un tableau formaté
        $servicePartialMock->shouldReceive('__invoke')
            ->andReturn([
                'Unnamed: 2' => 'Paris',
                'Unnamed: 4' => 1000,
                'Unnamed: 7' => 800,
                'Unnamed: 9' => 600,
                'Unnamed: 10' => 1500000.50,
                'Unnamed: 11' => 200,
                'Unnamed: 12' => 500000.75,
                'codeinsee' => '75056',
            ]);

        // 4. Appelle la méthode __invoke
        $result = $servicePartialMock->__invoke();

        // 5. Vérifie que le résultat est correct
        $this->assertIsArray($result);
        $this->assertEquals('Paris', $result['Unnamed: 2']);
        $this->assertEquals(1000, $result['Unnamed: 4']);
        $this->assertEquals(800, $result['Unnamed: 7']);
        $this->assertEquals(600, $result['Unnamed: 9']);
        $this->assertEquals(1500000.50, $result['Unnamed: 10']);
        $this->assertEquals(200, $result['Unnamed: 11']);
        $this->assertEquals(500000.75, $result['Unnamed: 12']);
        $this->assertEquals('75056', $result['codeinsee']);
    }

    #[Test]
    public function it_throws_exception_when_no_data_found()
    {
        // 1. Instancie la classe avec un code INSEE
        $getIncomingTax = new GetIncomingTax('75056');

        // 2. Utilise un mock partiel pour remplacer les méthodes
        $servicePartialMock = Mockery::mock($getIncomingTax)->makePartial();
        $servicePartialMock->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode __invoke pour lever une exception
        $servicePartialMock->shouldReceive('__invoke')
            ->andThrow(new \Exception("No data found for code insee: 75056"));

        // 4. Vérifie qu'une exception est levée
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No data found for code insee: 75056");

        // 5. Appelle la méthode __invoke
        $servicePartialMock->__invoke();
    }

    #[Test]
    public function it_logs_errors_when_exception_occurs()
    {
        // 1. Instancie la classe avec un code INSEE
        $getIncomingTax = new GetIncomingTax('75056');

        // 2. Utilise un mock partiel pour remplacer les méthodes
        $servicePartialMock = Mockery::mock($getIncomingTax)->makePartial();
        $servicePartialMock->shouldAllowMockingProtectedMethods();

        // 3. Mock la méthode protégée parseCodeInsee pour retourner un tableau avec les bons codes
        $servicePartialMock->shouldReceive('parseCodeInsee')
            ->andReturn(['departement_code' => '75', 'city_code' => '056']);

        // 4. Mock la méthode protégée getByCsv pour lever une exception avec le bon message
        $servicePartialMock->shouldReceive('getByCsv')
            ->with(['departement_code' => '75', 'city_code' => '056'])
            ->andThrow(new \Exception('No data found for code insee: 75056'));

        // 5. Espionne le Log pour vérifier que l'erreur est loggée avec le bon message
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'No data found for code insee: 75056');
            });

        // 6. Vérifie qu'une exception est levée avec le bon message
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No data found for code insee: 75056');

        // 7. Appelle la méthode __invoke
        $servicePartialMock->__invoke();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
