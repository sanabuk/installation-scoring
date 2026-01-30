<?php

namespace App\Services\Geographic\Service;

use App\Services\Geographic\Contract\WeatherServiceInterface;
use App\Services\Geographic\DTO\WeatherDataDTO;
use App\Services\Geographic\Scraper\GetWeatherHistoric;

class WeatherService implements WeatherServiceInterface
{
    /**
     * @return WeatherDataDTO[]
     */
    public function getWeatherData(float $latitude, float $longitude): array
    {
        $scraper = new GetWeatherHistoric($latitude, $longitude);
        $weatherDataArray = $scraper();

        $weatherDataDTOs = [];
        foreach ($weatherDataArray['daily']['time'] as $index => $time) {
            $weatherDataDTOs[] = new WeatherDataDTO(
                $weatherDataArray['daily']['temperature_2m_max'][$index],
                $weatherDataArray['daily']['temperature_2m_min'][$index],
                $weatherDataArray['daily']['temperature_2m_mean'][$index],
                $weatherDataArray['daily']['sunshine_duration'][$index],
                $weatherDataArray['daily']['weathercode'][$index],
                $weatherDataArray['daily']['precipitation_sum'][$index],
                new \DateTime($time)
            );
        }

        return $weatherDataDTOs;
    }
}

