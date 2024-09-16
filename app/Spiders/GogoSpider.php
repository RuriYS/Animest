<?php

namespace App\Spiders;

use App\Processors\GogoProcessor;
use App\Utils\CateParser;
use Generator;
use Illuminate\Support\Facades\Log;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use Symfony\Component\DomCrawler\Crawler;

class GogoSpider extends BasicSpider
{
    protected function initialRequests(): array
    {
        return [
            new Request(
                'GET',
                'https://anitaku.pe' . $this->context['uri'],
                [$this, 'parse']
            )
        ];
    }

    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"]
        ],
    ];

    public array $extensions = [];
    public array $itemProcessors = [
        GogoProcessor::class
    ];
    public function parse(Response $response): Generator
    {
        file_put_contents(public_path('gogo_content.html'), $response->html());

        $path = (string) parse_url($response->getUri(), PHP_URL_PATH);
        $paths = explode("/", $path);

        if ($response->getStatus() === 200) {
            switch ($paths[1]) {
                case 'category':
                    $id = $paths[2];

                    $paginationId = $response->filter('input#movie_id')->attr('value');
                    $paginationAlias = $response->filter('input#alias_anime')->attr('value');
                    $paginationDefaultIndex = $response->filter('input#default_ep')->attr('value');
                    $paginationNode = $response->filter('#episode_page > li:nth-child(1) > a');
                    $paginationStart = $paginationNode->attr('ep_start');
                    $paginationEnd = $paginationNode->attr('ep_end');
                    $params = http_build_query([
                        'ep_start' => $paginationStart,
                        'ep_end' => $paginationEnd,
                        'id' => $paginationId,
                        'default_ep' => $paginationDefaultIndex,
                        'alias' => $paginationAlias
                    ]);

                    yield $this->request('GET', "https://ajax.gogocdn.net/ajax/load-list-episode?$params", 'parseEpisodeList');

                    yield $this->item([
                        'description' => $response->filter('.anime_info_body_bg .description')->text(),
                        'length' => intval($response->filter('#episode_page a')->last()->attr('ep_end')),
                        'genres' => $response->filter('.anime_info_body_bg p.type:nth-child(7) a')->each(fn(Crawler $node) => trim(preg_replace('/[,]/', '', $node->text()))),
                        'id' => $id,
                        'language' => str_ends_with($id, '-dub') ? 'dub' : 'sub',
                        'names' => $response->filter('.anime_info_body_bg p.type:nth-child(10) a')->text(),
                        'origin' => null,
                        'season' => CateParser::parseSeason($response->filter('.anime_info_body_bg p.type:nth-child(4) a')->attr('href')),
                        'splash' => $response->filter('.anime_info_body_bg img')->attr('src'),
                        'status' => $response->filter('.anime_info_body_bg p.type:nth-child(9) a')->text(),
                        'title' => $response->filter('.anime_info_body_bg h1')->text(),
                        'year' => $response->filter('.anime_info_body_bg p.type:nth-child(8)')->innerText(),
                    ]);
                    break;
                case 'filter.html':
                    yield $this->item(
                        $response->filter('.items li')->each(function (Crawler $node) {
                            $releasedNode = $node->filter('.released');
                            $releasedNode ? preg_match('/\d+/', $releasedNode->text(), $matches) : null;
                            $year = $matches[0] ?? null;
                            return [
                                'image' => $node->filter('.img img')->attr('src'),
                                'title' => $node->filter('.name a[title]')->text(),
                                'id' => explode('/', $node->filter('.name a[href]')->attr('href'))[2],
                                'year' => $year,
                            ];
                        })
                    );
                    break;
                default:
                    yield $this->item([
                        'error' => 'Invalid path specified',
                    ]);
                    break;
            }


        } else {
            yield $this->item([
                'error' => "Title not found."
            ]);
        }
    }

    public function parseEpisodeList(Response $response): Generator
    {
        file_put_contents(public_path('episode_list.html'), $response->html());

        $href = $response->filter('#episode_related a')->attr('href');

        if (preg_match('/\/([^\/]+)-episode-\d+$/', $href, $matches)) {
            $alias = $matches[1];
            yield $this->item([
                'alias' => $alias,
            ]);
        } else {
            Log::warning("Could not extract alias ID: " . $href);
        }
    }
}
