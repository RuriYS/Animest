<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;

class MALSpider extends BasicSpider {
    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"],
        ],
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    protected function initialRequests(): array {
        $action   = $this->context['action'];
        $keyword   = $this->context['keyword'];
        $base_url = 'myanimelist.net';
        $path     = null;
        $requests = [];

        switch ($action) {
            case 'search':
                $path = '/search/prefix.json';
                $params = http_build_query([
                    'type' => 'anime',
                    'keyword' => $keyword
                ]);
                $requests[] = new Request(
                    'GET',
                    "$base_url$path?$params",
                    function (Response $response) {
                        return $this->parseSearch($response);
                    }
                );
        }

        return $requests;
    }

    public function parseSearch(Response $response) {
        $body = $response->getBody();

        if (!empty($body)) {
            $json = json_decode($body, true);
            $items = array_merge(...array_map(fn ($item) => $item['items'], $json['categories']));
            yield $this->item($items);
        }
    }

    public function parse(Response $response): Generator {
        yield null;
    }
}
