<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEpisodes;
use Illuminate\Console\Command;

class GetEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:episodes {id} {start} {end?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the Episodes for a Title';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $start = $this->argument('start');
        $end = $this->argument('end');
        $range = (int) $end ?: $start;

        for ($i = $start ?: 1; $i <= $range; $i++) {

            $episodeId = "{$id}-episode-{$i}";

            ProcessEpisodes::dispatch($episodeId);

            $this->info("Dispatched job for {$episodeId}");
        }

        $this->info('All jobs have been dispatched successfully.');
    }
}
