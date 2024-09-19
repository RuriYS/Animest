<?php

namespace App\Http\Controllers;

use App\Spiders\GogoSpider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RoachPHP\Roach;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');
        $sort = $request->input('s') ?? 'title_az';

        $cacheKey = "search_results:{$q}:{$sort}";

        $results = Cache::remember($cacheKey, now()->addHours(4), function () use ($q, $sort) {
            $params = http_build_query([
                'keyword' => $q,
                'sort' => $sort
            ]);

            $items = Roach::collectSpider(
                GogoSpider::class,
                context: [
                    'uri' => 'https://' . config('app.urls.gogo') . "/filter.html?$params"
                ]
            );

            return array_merge(...array_map(fn($item) => $item->all(), $items));
        });

        return response()->json([
            'query' => $q,
            'count' => count($results),
            'results' => $results
        ]);
    }
}
