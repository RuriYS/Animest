<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTitle;
use App\Spiders\GogoSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;

class Gogo extends Command {
    protected $signature = 'scraper:gogo {uri}';

    protected $description = 'Gogo scraper';

    public function handle() {
        $uri = $this->argument('uri');
        $this->info('GogoScraper started with URI: ' . $uri);

        $results = Roach::collectSpider(
            GogoSpider::class,
            context: ['uri' => $uri],
        );

        $result = array_map(fn($item) => $item->all(), $results);
        $this->info('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
    }
}
