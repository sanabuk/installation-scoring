<?php

namespace App\Services\Concurrence\Scraper;

use App\Services\Tools\CsvQueryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;

class GetAmap
{
    protected string $departement_code;
    protected string $city_name;

    public function __construct(string $departement_code, string $city_name)
    {
        $this->departement_code = $departement_code;
        $this->city_name = $city_name;
    }

    public function __invoke(): array
    {
        try {
            return $this->getByCsv();
        } catch (\Exception $e) {
            Log::error('Error in GetAmap class: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getByCsv(): array
    {
        $csvQueryService = new CsvQueryService('amap.csv');
        $results = $csvQueryService
            ->where('Departement', $this->departement_code)
            ->where('Ville', strtolower($this->city_name))
            ->get();
        
        return $results->toArray();
    }
}