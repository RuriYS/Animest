<?php

namespace App\Spiders;

use Exception;
use Generator;
use App\Utils\Helper;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use App\Processors\GogoProcessor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;

/*
 |------------------------------------------------------------------------------------------------------------------
 |
 |    /$$$$$$                                 /$$$$$$
 |   /$$__  $$                               /$$__  $$
 |   | $$  \__/  /$$$$$$   /$$$$$$   /$$$$$$ | $$  \__/  /$$$$$$$  /$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$
 |   | $$ /$$$$ /$$__  $$ /$$__  $$ /$$__  $$|  $$$$$$  /$$_____/ /$$__  $$|____  $$ /$$__  $$ /$$__  $$ /$$__  $$
 |   | $$|_  $$| $$  \ $$| $$  \ $$| $$  \ $$ \____  $$| $$      | $$  \__/ /$$$$$$$| $$  \ $$| $$$$$$$$| $$  \__/
 |   | $$  \ $$| $$  | $$| $$  | $$| $$  | $$ /$$  \ $$| $$      | $$      /$$__  $$| $$  | $$| $$_____/| $$
 |   |  $$$$$$/|  $$$$$$/|  $$$$$$$|  $$$$$$/|  $$$$$$/|  $$$$$$$| $$     |  $$$$$$$| $$$$$$$/|  $$$$$$$| $$
 |   \______/  \______/  \____  $$ \______/  \______/  \_______/|__/      \_______/| $$____/  \_______/|__/
 |                       /$$  \ $$                                                 | $$
 |                       |  $$$$$$/                                                 | $$
 |                       \______/                                                  |__/
 |-------------------------------------------------------------------------------------------------------------------
 */

class GogoSpider extends BasicSpider {
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

    public array $itemProcessors = [
        GogoProcessor::class,
    ];

    protected function initialRequests(): array {
        $args     = $this->context['args'] ?? null;
        $base_url = $this->context['base_url'];
        $params   = $args ? http_build_query($args['params']) : null;
        $uri      = $params ? "$base_url?$params" : $base_url;
        $path     = parse_url($uri, PHP_URL_PATH);
        $paths    = array_filter(explode("/", $path));
        $requests = [];

        $requests[] = isset($paths[1]) ? match ($paths[1]) {
            'category'    => new Request(
                method: 'GET',
                uri: $uri,
                parseMethod: function (Response $response) use ($paths) {
                        return $this->parseCategory($response, $paths[2] ?? null);
                    },
            ),
            'filter.html' => new Request(
                method: 'GET',
                uri: $uri,
                parseMethod: function (Response $response) use ($args) {
                        return $this->parseFilterResults($response, $args);
                    },
            ),
        } : new Request(
            method: 'GET',
            uri: $uri,
            parseMethod: [$this, 'parseError'],
            options: ['error' => 'Invalid Request'],
        );

        return $requests;
    }

    public function parseCategory(Response $response, string $id): Generator {
        $paginationNode = $response->filter('#episode_page > li:nth-child(1) > a');

        $paginationData = [
            'paginationId'           => $response->filter('input#movie_id')->first()?->attr('value'),
            'paginationAlias'        => $response->filter('input#alias_anime')->first()?->attr('value'),
            'paginationDefaultIndex' => $response->filter('input#default_ep')->first()?->attr('value'),
            'paginationStart'        => $paginationNode?->attr('ep_start'),
            'paginationEnd'          => $paginationNode?->attr('ep_end'),
        ];

        if (in_array(null, array_keys($paginationData))) {
            throw new Exception('No pagination data found');
        }

        $params = http_build_query([
            'ep_start'   => $paginationData['paginationStart'],
            'ep_end'     => $paginationData['paginationEnd'],
            'id'         => $paginationData['paginationId'],
            'default_ep' => $paginationData['paginationDefaultIndex'],
            'alias'      => $paginationData['paginationAlias'],
        ]);

        yield $this->request('GET', "https://ajax.gogocdn.net/ajax/load-list-episode?$params", 'parseEpisodeList');

        $items = [
            'description' => $response->filter('.anime_info_body_bg .description')->text(),
            'length'      => intval($response->filter('#episode_page a')->last()->attr('ep_end')),
            'genres'      => $response->filter('.anime_info_body_bg p.type:nth-child(7) a')->each(fn(Crawler $node) => trim(preg_replace('/[,]/', '', $node->text()))),
            'id'          => $id,
            'language'    => str_ends_with($id, '-dub') ? 'dub' : 'sub',
            'names'       => $response->filter('.anime_info_body_bg p.type:nth-child(10) a')->text(),
            'origin'      => null,
            'season'      => Helper::parseSeason($response->filter('.anime_info_body_bg p.type:nth-child(4) a')->attr('href')),
            'splash'      => $response->filter('.anime_info_body_bg img')->attr('src'),
            'status'      => $response->filter('.anime_info_body_bg p.type:nth-child(9) a')->text(),
            'title'       => $response->filter('.anime_info_body_bg h1')->text(),
            'year'        => $response->filter('.anime_info_body_bg p.type:nth-child(8)')->innerText(),
        ];

        yield $this->item($items);
    }

    public function parseFilterResults(Response $response, array $args): Generator {
        $results = $response->filter('.items li')->each(
            function (Crawler $node) {
                $releasedNode = $node->filter('.released');
                $releasedNode ? preg_match('/\d+/', $releasedNode->text(), $matches) : null;
                $year = $matches[0] ?? null;

                return [
                    'image' => $node->filter('.img img')->attr('src'),
                    'title' => $node->filter('.name a[title]')->text(),
                    'id'    => explode('/', $node->filter('.name a[href]')->attr('href'))[2],
                    'year'  => $year,
                ];
            }
        );

        yield $this->item([
            'results' => $results,
        ]);
    }

    public function parseEpisodeList(Response $response): Generator {
        // file_put_contents(public_path('episode_list.html'), $response->html());

        $href         = trim($response->filter('#episode_related a')->attr('href'));
        $id_fragments = Helper::parseEpisodeID($href);
        if ($id_fragments) {
            yield $this->item([
                'alias' => $id_fragments['alias'],
            ]);
        } else {
            Log::warning("Could not extract alias ID: $href");
            yield $this->item([
                'alias' => null,
            ]);
        }
    }

    public function parse(Response $response): Generator {
        yield null;
    }
}
