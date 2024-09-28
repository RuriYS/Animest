<?php

namespace App\Http\Controllers;

use App\Spiders\GogoSpider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class SearchController extends ControllerAbstract {
    public function search(Request $request) {
        $query    = $request->input('q') ?? '';
        $page     = $request->input('p') ?? '';
        $sort     = $request->input('s') ?? 'title_az';
        $limit    = $request->integer('l') ?? 20;
        $cacheKey = "search_results:{$query}:{$page}:{$sort}";

        $args = [
            'params' => [
                'keyword' => $query,
                'sort'    => $sort,
                'page'    => $page,
            ],
            'limit'  => $limit,
        ];

        $data = Cache::remember(
            $cacheKey,
            now()->addHours(4),
            function () use ($args) {
                $items = Roach::collectSpider(
                    GogoSpider::class,
                    context: [
                        'base_url' => sprintf(
                            'https://%s/filter.html',
                            config('app.urls.gogo'),
                        ),
                        'args'     => $args,
                    ],
                );

                return array_merge(
                    ...array_map(
                        fn($item) => $item->all(),
                        $items,
                    ),
                );
            }
        );

        return response()->json([
            'query' => $query,
            'count' => count($data['results']),
            ...$data,
        ]);
    }
}
