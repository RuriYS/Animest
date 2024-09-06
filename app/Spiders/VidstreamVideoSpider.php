<?php

namespace App\Spiders;

use App\Processors\VidstreamItemProcessor;
use App\Utils\Ajax;
use Generator;
use Log;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
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
                'https://s3taku.com/videos/' . $this->context['id'] ?? "no-game-no-life-episode-1",
                [$this, 'parse']
            )
        ];
    }

    public int $concurrency = 2;

    public int $requestDelay = 0;

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"]
        ],
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public array $itemProcessors = [
        VidstreamItemProcessor::class,
    ];

    /**
     * --------------------------------------------------------------------------
     * # Video episode page parser
     * --------------------------------------------------------------------------
     *
     * ### Returns
     * 1. anime
     * > eg; No Game No Life
     *
     * 2. brief (description)
     *
     * 3. related
     * > - title
     * > - episode_id
     * > - date_added
     * > - splash
     *
     * @param \RoachPHP\Http\Response $response
     * @return \Generator
     */
    public function parse(Response $response): Generator
    {
        $iframe = $response->filter('.play-video iframe[src]');

        if (!$iframe->getNode(0)) {
            yield $this->item(['errors' => "Didn't find anything :<"]);
        } else {
            $related =  $response->filter('.listing.lists li')->each(
                fn (Crawler $node) => [
                        'title'         =>  urldecode($node->filter('.name')->text()),
                        'episode_id'    =>  explode('/videos/', urldecode($node->filter('a')->first()->attr('href')))[1],
                        'date_added'    =>  $node->filter('.meta .date')->text(),
                        'splash'        =>  $node->filter('.img .picture img')->attr('src'),
                    ]
                );
            yield $this->request('GET', $iframe->attr('src'), 'parseIframe');
            yield $this->item([
                'anime'             => $response->filter('.video-details .date')->text(),
                'brief'             => $response->filter('.video-details .post-entry')->text(),
                'related'           => $related,
                ]
            );
        }
    }

    /**
     * --------------------------------------------------------------------------
     * # Video Iframe parser
     * --------------------------------------------------------------------------
     *
     * This is ran after we crawl through the main episode page
     * The difference is that this retrieves the video itself
     *
     * ### Returns
     * 1. title
     * > eg; No Game No Life Episode 1
     *
     * 2. episode_id
     * > eg; no-game-no-life-episode-1
     *
     * 3. video_id
     * > NDI3NzY=
     *
     * 4. type
     * > eg; SUB/DUB
     *
     * 5. token
     * > This is an **encrypted token** which is in Base64 format
     * > that needs to be decoded using AES decryption.
     * > ```aes->decrypt(base64_decode(token))```
     *
     *
     * @param \RoachPHP\Http\Response $response
     * @return \Generator
     */
    public function parseIframe(Response $response): Generator
    {
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
            'title'             =>  urldecode(str_replace('+', ' ', $response->filter('#title')->attr('value'))),
            'episode_id'        =>  str_replace(' ', '-', $episode_id),
            'video_id'          =>  $response->filter('#id')->attr('value'),
            'type'              =>  $response->filter('#typesub')->attr('value'),
            // 'download_uri'      => "https://s3taku.com/download?id=$token",
            // 'token'             =>  $token,
            // 'encryption_key'    =>  explode('container-', $response->filter("body[class^='container-']")->attr('class'))[1],
            // 'iv_key'            =>  explode('container-', $response->filter("div[class*='wrapper container-']")->attr('class'))[1],
            // 'decryption_key'    =>  explode('videocontent-', $response->filter("div[class*='videocontent-']")->attr('class'))[1],
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
        // yield $this->item([
        //     'uri' => $response->getUri(),
        //     'options' => $response->getRequest()->getOptions(),
        // ]);

        $ajax = new Ajax(
            config('app.decryption_key'),
            config('app.iv_key'),
        );

        yield $this->item([
            'stream_data' => $ajax->decryptAjaxData(json_decode($response->getBody(), true)['data'])
        ]);
    }
}
