<?php

namespace App\Jobs;

use App\Spiders\GogoSpider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class GogoProcess implements ShouldQueue
{
    use Queueable;
    protected string $uri;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function handle(): void
    {
        $uri = $this->uri;
        Log::info("GogoProcess started with URI: " . $uri);

        $results = Roach::collectSpider(
            GogoSpider::class,
            context: ['uri' => $uri]
        );
        $result = array_merge(
            ...array_map(fn($item) => $item->all(), $results)
        );
        Log::info('GogoProcess: ' . json_encode($result, JSON_PRETTY_PRINT));
    }
}
