<?php

namespace App\Spiders;

use App\Processors\VidstreamProcessor;
use App\Utils\Ajax;
use App\Utils\Dateparser;
use Generator;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use Symfony\Component\DomCrawler\Crawler;

class VidstreamSpider extends BasicSpider {
    protected function initialRequests(): array {
        return [
            new Request(
                'GET',
                $this->context['base_url'] . $this->context['id'],
                [$this, 'parse'],
            ),
        ];
    }

    public int $concurrency = 10;

    public int $requestDelay = 0;

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"],
        ],
    ];

    public array $itemProcessors = [
        VidstreamProcessor::class,
    ];

    public array $extensions = [];

    public function parse(Response $response): Generator {

        $dateparser = new Dateparser();
        $iframe     = $response->filter('.play-video iframe[src]');

        if ($iframe->count() >= 1) {
            yield $this->item([
                'title'       => $response->filter('.video-details .date')->innerText(true),
                'description' => $response->filter('.video-details #rmjs-1')->innerText(true),
                'episode_id'  => $this->context['id'],
            ]);

            yield $this->item([
                'episodes' =>
                    $response->filter('.listing.lists li')->each(
                        fn(Crawler $node) => [
                            'episode_id' => explode('/videos/', urldecode($node->filter('a')->first()->attr('href')))[1],
                            'title'      => urldecode($node->filter('.name')->text()),
                            'date_added' => $dateparser->parseDate($node->filter('.meta .date')->text()),
                            'splash'     => $node->filter('.img .picture img')->attr('src'),
                        ]
                    ),
            ]);

            yield $this->request('GET', $iframe->attr('src'), 'parseIframe');
        } else {
            yield $this->item([
                'error' => 'Episode not found.',
            ]);
        }
    }

    public function parseIframe(Response $response): Generator {
        $ajax = new Ajax(
            config('app.encryption_key'),
            config('app.iv_key'),
        );

        $token    = $response->filter('script[data-name="episode"]')->attr('data-value');
        $token    = $ajax->decryptToken($token);
        $video_id = $response->filter('#id')->attr('value');

        yield $this->item([
            'title'        => urldecode(str_replace('+', ' ', $response->filter('#title')->attr('value'))),
            'video_id'     => $response->filter('#id')->attr('value'),
            'type'         => $response->filter('#typesub')->attr('value'),
            'download_url' => "https://s3taku.com/download?id=$token",
        ]);

        $params = $ajax->generateAjaxParams(
            $token,
            $video_id,
        );

        yield $this->request('GET', "https://s3taku.com/encrypt-ajax.php?$params", 'parseVideoData', [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
            ],
        ]);
    }

    public function parseVideoData(Response $response) {
        $ajax = new Ajax(
            config('app.decryption_key'),
            config('app.iv_key'),
        );

        yield $this->item([
            'stream_data' => $ajax->decryptAjaxData(json_decode($response->getBody(), true)['data']),
        ]);
    }
}
