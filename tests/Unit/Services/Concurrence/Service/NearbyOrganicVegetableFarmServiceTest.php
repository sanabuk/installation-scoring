<?php

namespace Tests\Unit\Services\Concurrence\Service;

use App\Services\Concurrence\DTO\NearbyOrganicVegetableFarmDTO;
use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use App\Services\Concurrence\Service\NearbyOrganicVegetableFarmService;
use App\Services\Tools\GetCodeInseeFromLatAndLon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NearbyOrganicVegetableFarmServiceTest extends TestCase
{
    #[Test]
    public function it_returns_an_array_of_nearby_organic_vegetable_farm_dto()
    {
        $mockScraper = Mockery::mock(GetNearbyOrganicVegetableFarms::class);
        $mockScraper->shouldReceive('__invoke')
            ->once()
            ->andReturn(new JsonResponse([
                'items' => [
                    [
                        'nom' => 'Ferme Bio 1',
                        'nomAnnuaire' => 'Ferme Bio 1 Annuaire',
                        'siret' => '12345678901234',
                        'datePremierEngagement' => '2020-01-01',
                        'telephone' => '0123456789',
                        'telephoneNational' => '0987654321',
                        'gerant' => 'Jean Dupont',
                        'adressesOperateurs' => [
                            [
                                'lieu' => '1 Rue de la Ferme',
                                'codePostal' => '75001',
                                'ville' => 'Paris',
                                'location' => [
                                    'lat' => 48.8566,
                                    'lon' => 2.3522,
                                ],
                            ],
                        ],
                        'siteWebs' => [
                            ['url' => 'https://ferme-bio.fr'],
                        ],
                        'annuaireActivites' => [
                            ['id' => 18],
                        ],
                        'annuaireInformation' => [
                            'venteProsGros' => true,
                            'venteProsDetail' => false,
                            'venteParticuliers' => true,
                            'venteRestauCollective' => false,
                            'venteRestauCommerciale' => true,
                            'horaires' => [],
                        ],
                        'adresseOperateur' => [
                            'distance' => 10.5,
                        ],
                    ],
                ],
            ]));
        
        $service = Mockery::mock(NearbyOrganicVegetableFarmService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('createScraper')
            ->with(48.8566, 2.3522, 15)
            ->andReturn($mockScraper);

        $service->shouldReceive('getCodeInseeFromLatAndLon')
            ->with(48.8566, 2.3522)
            ->andReturn('75056');
        
        $result = $service->getNearbyOrganicVegetableFarms(48.8566, 2.3522, 15);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(NearbyOrganicVegetableFarmDTO::class, $result[0]);
        $this->assertEquals('Ferme Bio 1', $result[0]->getName());
        $this->assertEquals('75056', $result[0]->getCodeInsee());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

}
