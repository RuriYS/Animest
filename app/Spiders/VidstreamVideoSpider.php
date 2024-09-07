<?php

namespace App\Spiders;

use App\Processors\VidstreamItemProcessor;
use App\Utils\Ajax;
use App\Utils\Dateparser;
use Generator;
use Log;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use Symfony\Component\DomCrawler\Crawler;

/**
 * --------------------------------------------------------------------------
 * Vidstream Spider
 * --------------------------------------------------------------------------
 *
 */
class VidstreamVideoSpider extends BasicSpider
{
    protected function initialRequests(): array
    {
        return [
            new Request(
                'GET',
                'https://s3taku.com/videos/' . $this->context['id'],
                [$this, 'parse']
            )
        ];
    }

    public int $concurrency = 10;

    public int $requestDelay = 0;

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"]
        ],
    ];

    public array $itemProcessors = [
        VidstreamItemProcessor::class,
    ];

    public array $extensions = [];

    public function parse(Response $response): Generator
    {
        Log::debug("Parsing {$this->context['id']}");

        $dateparser = new Dateparser();
        $iframe = $response->filter('.play-video iframe[src]');

        if (!$iframe->getNode(0)) {
            yield $this->item(['errors' => "Didn't find anything :<"]);
        } else {
            yield $this->item([
                'related' => $response->filter('.listing.lists li')->each(
                    fn(Crawler $node) => [
                        explode('/videos/', urldecode($node->filter('a')->first()->attr('href')))[1] => [
                            'title' => urldecode($node->filter('.name')->text()),
                            'date_added' => $dateparser->parseDate($node->filter('.meta .date')->text()),
                            'splash' => $node->filter('.img .picture img')->attr('src'),
                        ]
                    ]
                )
            ]);
            yield $this->request('GET', $iframe->attr('src'), 'parseIframe');
        }
    }

    public function parseIframe(Response $response): Generator
    {
        // Log::debug("Parsing iframe");
        $ajax = new Ajax(
            config('app.encryption_key'),
            config('app.iv_key'),
        );

        $token = $response->filter('script[data-name="episode"]')->attr('data-value');
        $token = $ajax->decryptToken($token);
        $video_id = $response->filter('#id')->attr('value');
        $url = $response->getUri();
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $episode_id = preg_replace('/[^a-z\s\d]/', '', strtolower(urldecode($query['title'])));

        yield $this->item([
            'title' => urldecode(str_replace('+', ' ', $response->filter('#title')->attr('value'))),
            'episode_id' => str_replace(' ', '-', $episode_id),
            'video_id' => $response->filter('#id')->attr('value'),
            'type' => $response->filter('#typesub')->attr('value'),
            'download_uri' => "https://s3taku.com/download?id=$token",
        ]);

        $params = $ajax->generateAjaxParams(
            $token,
            $video_id,
        );

        yield $this->request('GET', "https://s3taku.com/encrypt-ajax.php?$params", 'parseVideoData', [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);
    }

    public function parseVideoData(Response $response)
    {
        // Log::debug("Parsing videodata");
        $ajax = new Ajax(
            config('app.decryption_key'),
            config('app.iv_key'),
        );

        yield $this->item([
            'stream_data' => $ajax->decryptAjaxData(json_decode($response->getBody(), true)['data'])
        ]);
    }
}
