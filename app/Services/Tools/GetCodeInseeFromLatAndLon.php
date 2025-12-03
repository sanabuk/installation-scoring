<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetCodeInseeFromLatAndLon
{
    public string $lat;
    public string $lon;

    public function __construct(string $lat, string $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function __invoke(): ?string
    {
        try {
            $response = Http::get('https://api-adresse.data.gouv.fr/reverse?lat='.$this->lat.'&lon='.$this->lon.'&limit=1');
            $data = $response->json();
            $code_insee = $data['features'][0]['properties']['citycode'] ?? null;   
            return $code_insee;
        } catch (\Exception $e) {
            Log::error('Error in GetCodeInseeFromLatAndLon class: ' . $e->getMessage());
            throw $e;
        }
        
    }
}