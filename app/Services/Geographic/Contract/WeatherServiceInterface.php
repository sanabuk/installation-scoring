<?php

namespace App\Services\Geographic\Contract;

use App\Services\Geographic\DTO\WeatherDataDTO;

Interface WeatherServiceInterface
{
    /**
     * @return WeatherDataDTO[]
     */
    public function getWeatherData(float $latitude, float $longitude): array;
}