<?php

namespace App\Console\Commands;

use App\Services\Geographic\Scraper\GetNearbyMunicipalities;
use App\Services\Geographic\Scraper\GetPopulation;
use Illuminate\Console\Command;

class makeScoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-scoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //$this->info('NEARBY POP');
        //$nearbyMunicipalities = new GetNearbyMunicipalities(47.39532, 0.74771, 2);
        //$municipalities = $nearbyMunicipalities()->getOriginalContent();
        
        $this->info('POPULATION');
        $population = new GetPopulation();
        $popData = $population("37261");
    }
}