<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RoachPHP\Roach;

class TitleController extends Controller
{
    public function show(string $id)
    {
        $title = Title::find($id);

        if (!$title) {
            ProcessTitle::dispatchSync($id);
            $title = Title::find($id);
        }

        return response()->json([
            'errors' => $title ? null : 'Not found',
            'query' => $id,
            'result' => $title?->toArray()
        ]);
    }

    public function process(string $id)
    {
        ProcessTitle::dispatchSync($id);
        return response()->json([
            'message' => 'Job dispatched'
        ]);
    }


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
                context: ['uri' => "/filter.html?$params"]
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
