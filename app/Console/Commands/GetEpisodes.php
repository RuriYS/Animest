<?php

namespace App\Console\Commands;

use App\Jobs\GetVideo;
use Illuminate\Console\Command;

class GetEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:videos {id} {start} {end?}';

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
        $id = $this->argument('id');
        $start = $this->argument('start');
        $end = $this->argument('end');
        $range = (int) $end ?: $start;

        for ($i = $start ?: 1; $i <= $range; $i++) {

            $episodeId = "{$id}-episode-{$i}";

            GetVideo::dispatch($episodeId);

            $this->info("Dispatched job for {$episodeId}");
        }

        $this->info('All jobs have been dispatched successfully.');
    }
}
