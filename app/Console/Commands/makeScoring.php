<?php

namespace App\Console\Commands;

use App\Services\MunicipalityInformations;
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
        $this->info('START');
        $finder = new MunicipalityInformations();

        $communes = $finder->getNearbyMunicipalities(47.39532, 0.74771, 5);

        $this->info($communes);
    }
}
