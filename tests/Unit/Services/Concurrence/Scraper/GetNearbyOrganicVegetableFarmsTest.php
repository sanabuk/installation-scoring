<?php

namespace Tests\Unit\Services\Concurrence\Scraper;

use App\Services\Concurrence\Scraper\GetNearbyOrganicVegetableFarms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetNearbyOrganicVegetableFarmsTest extends TestCase
{
    #[Test]
    public function it_makes_a_correct_api_request_and_returns_json_response()
    {
        Http::fake([
            'https://annuaire-back.agencebio.org/operateurs?*' => Http::response(['data' => ['ferme1', 'ferme2']], 200),
        ]);

        $scraper = new GetNearbyOrganicVegetableFarms(48.8566, 2.3522, 15);

        $response = $scraper();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(['data' => ['ferme1', 'ferme2']], $response->getData(true));
    }

    #[Test]
    public function it_handles_api_errors_correctly()
    {
        Http::fake([
            'https://annuaire-back.agencebio.org/operateurs?*' => Http::response([], 500),
        ]);

        $scraper = new GetNearbyOrganicVegetableFarms(48.8566, 2.3522, 15);

        try {
            $scraper();
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertTrue(true); // L'exception a bien été levée
        }
    }

    #[Test]
    public function it_logs_errors_when_api_request_fails()
    {
        Http::fake([
            'https://annuaire-back.agencebio.org/operateurs?*' =>
                function () {
                    throw new \Exception('API request failed');
                },
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Error in GetNearbyOrganicVegetableFarms class');
            });

        $scraper = new GetNearbyOrganicVegetableFarms(48.8566, 2.3522, 15);

        $this->expectException(\Exception::class);

        $scraper();
    }
}
