<?php

namespace App\Services\Geographic\DTO;

class WeatherDataDTO
{
    public float $temperature_2m_max;
    public float $temperature_2m_min;
    public float $temperture_2m_mean;
    public float $sunshine_duration;
    public float $weather_code;
    public string $precipitation_sum;
    public \Datetime $time;

    public function __construct(
        float $temperature_2m_max,
        float $temperature_2m_min,
        float $temperture_2m_mean,
        float $sunshine_duration,
        float $weather_code,
        string $precipitation_sum,
        \Datetime $time
    ) {
        $this->temperature_2m_max = $temperature_2m_max;
        $this->temperature_2m_min = $temperature_2m_min;
        $this->temperture_2m_mean = $temperture_2m_mean;
        $this->sunshine_duration = $sunshine_duration;
        $this->weather_code = $weather_code;
        $this->preipitation_sum = $precipitation_sum;
        $this->time = $time;
    }
}