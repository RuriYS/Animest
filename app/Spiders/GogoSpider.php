<?php

namespace App\Spiders;

use App\Utils\AnimeParser;
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
                'https://anitaku.pe/category/' . $this->context['id'],
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
    public function parse(Response $response): Generator
    {
        if ($response->getStatus() === 200) {
            yield $this->item([
                'description' => $response->filter('.anime_info_body_bg .description')->text(),
                'length' => intval($response->filter('#episode_page a')->attr('ep_end')),
                'genres' => $response->filter('.anime_info_body_bg p.type:nth-child(7) a')->each(fn(Crawler $node) => trim(preg_replace('/[,]/', '', $node->text()))),
                'id' => $this->context['id'],
                'language' => str_ends_with($this->context['id'], '-dub') ? 'dub' : 'sub',
                'names' => $response->filter('.anime_info_body_bg p.type:nth-child(10) a')->text(),
                'origin' => null,
                'season' => AnimeParser::parseSeason($response->filter('.anime_info_body_bg p.type:nth-child(4) a')->attr('href')),
                'splash' => $response->filter('.anime_info_body_bg img')->attr('src'),
                'status' => $response->filter('.anime_info_body_bg p.type:nth-child(9) a')->text(),
                'title' => $response->filter('.anime_info_body_bg h1')->text(),
                'year' => $response->filter('.anime_info_body_bg p.type:nth-child(8)')->innerText(),
            ]);
        } else {
            yield $this->item([
                'error' => "Title not found."
            ]);
        }
    }
}
