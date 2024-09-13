<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTitle;
use Illuminate\Console\Command;

class GetTitle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:title {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the information about the Title';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        ProcessTitle::dispatch($id);
        $this->info("Dispatched job with Title ID: $id");
    }
}
