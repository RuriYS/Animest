<?php

namespace App\Spiders;

use App\Utils\CateParser;
use Generator;
use Illuminate\Support\Facades\Log;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use Symfony\Component\DomCrawler\Crawler;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;

class GogoAjaxSpider extends BasicSpider {
    protected function initialRequests(): array {
        return [
            new Request(
                method: 'GET',
                uri: $this->context['uri'],
                parseMethod: [$this, 'parse'],
            ),
        ];
    }

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"],
        ],
    ];

    public function parse(Response $response): Generator {
        $path  = parse_url($response->getUri(), PHP_URL_PATH);
        $paths = array_filter(explode("/", $path));

        if ($response->getStatus() && count($paths) >= 2) {
            $path = implode("/", array_slice($paths, 0, 2));

            Log::debug("Path: $path");
            switch ($path) {
                case 'ajax/page-recent-release-ongoing.html':
                    yield from $this->parsePopularReleases($response);
            }
        }
    }

    public function parsePopularReleases(Response $response): Generator {
        $pages = $response->filter('.pagination-list li')->each(function (Crawler $node) {
            return [
                // 'href' => $node->filter('a')->attr('href'),
                'index'      => $node->filter('a')->attr('data-page'),
                'isSelected' => $node->attr('class') === 'selected'
            ];
        });

        $list = $response->filter('.added_series_body > ul > li')->each(function (Crawler $node) {
            return [
                'id'                => CateParser::parseTitleId($node->filter('a')->attr('href') ?? ''),
                'genres'            => $node->filter('p.genres > a')->each(function (Crawler $subnode) {
                    return Cateparser::parseGenre($subnode->attr('href') ?? '');
                }),
                'latest_episode_id' => substr($node->filter('p:nth-of-type(2) > a')->attr('href'), 1),
                'thumbnail'         => CateParser::parseThumbnail($node->filter('.thumbnail-popular')->attr('style')),
                'title'             => $node->filter('a:nth-of-type(2)')->text(),
            ];
        });

        yield $this->item([
            'pages' => $pages,
            'list'  => $list,
        ]);
    }
}
