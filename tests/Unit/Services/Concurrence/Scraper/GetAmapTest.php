<?php

namespace Tests\Unit\Services\Concurrence\Scraper;

use App\Services\Concurrence\Scraper\GetAmap;
use Tests\TestCase;

class GetAmapTest extends TestCase
{
    #[Test]
    public function test_it_returns_an_array()
    {
        $getAmap = new GetAmap('35', 'saint-malo');
        
        $this->assertIsArray($getAmap());
        $this->assertNotEmpty($getAmap());
    }
    
    #[Test]
    public function test_it_returns_an_empty_array_when_no_match()
    {
        $getAmap = new GetAmap('99', 'NonExistentCity');
        
        $this->assertEmpty($getAmap());
    }
    
    #[Test]
    public function test_it_returns_an_array_with_matching_data()
    {
        $getAmap = new GetAmap('37', 'Tours');

        foreach ($getAmap() as $amap) {
            $this->assertSame('37', $amap['Departement']);
            $this->assertSame('tours', $amap['Ville']);
        }
    }
}