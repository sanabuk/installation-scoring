<?php

namespace App\Console\Commands;

use App\Services\Tools\AmapCsvFromAvenirBioWebsite;
use Illuminate\Console\Command;

class ScrapAmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:amap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command generate a file csv of amap from avenirbio website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $srapAmap = new AmapCsvFromAvenirBioWebsite();
        return $srapAmap->handler();
    }
}