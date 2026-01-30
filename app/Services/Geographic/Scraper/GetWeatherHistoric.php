<?php

namespace App\Services\Geographic\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class GetWeatherHistoric
{
    public float $latitude;
    public float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function __invoke(): array
    {
        $response = Http::get('https://archive-api.open-meteo.com/v1/archive', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'start_date' => Carbon::now()->subYears(5)->toDateString(),
            'end_date' => Carbon::now()->subDay()->toDateString(),
            'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean,sunshine_duration,weathercode,precipitation_sum',
            'timezone' => 'auto'
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch historic weather data');
        }
        //dump($response->json()['daily']);

        return $response->json();
    }
}