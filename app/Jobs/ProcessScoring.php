<?php

namespace App\Jobs;

use App\Services\ScoringHandler\ScoringHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessScoring implements ShouldQueue
{
    use Queueable;

    public float $lon;
    public float $lat;
    public string $email;

    /**
     * Create a new job instance.
     */
    public function __construct(float $lon, float $lat, string $email)
    {
        $this->lon = $lon;
        $this->lat = $lat;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $scoring_handler = new ScoringHandler($this->lat, $this->lon, $this->email);
        $scoring_handler->handler();
    }
}
