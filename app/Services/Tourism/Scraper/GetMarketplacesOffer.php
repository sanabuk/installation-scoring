<?php

namespace App\Services\Tourism\Scraper;

use App\Services\Tools\CsvQueryService;
use Illuminate\Support\Facades\Log;

class GetMarketplacesOffer
{
    private string $postal_code;

    public function __construct(string $postal_code)
    {
        $this->postal_code = $postal_code;
    }

    public function __invoke()
    {
         try {
            return $this->getFromCsv();
        } catch (\Exception $e) {
            Log::error('Error in GetMarketplacesOffer class: ' . $e->getMessage());
            throw $e;
        }        
    }

    private function getFromCsv(): array
    {
        $csvQueryService = new CsvQueryService('marches.csv');
        $results = $csvQueryService
            ->where('code_postal', $this->postal_code)
            ->get();
        
        return $results->toArray();
    }
}