<?php

namespace App\Http\Controllers;

use App\Spiders\GogoAjaxSpider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RoachPHP\Roach;

class AjaxController extends ControllerAbstract {
    public function popularReleases(Request $request) {
        $page = (int) $request->input(
            'page',
            1,
        );

        $refresh = filter_var(
            $request->input(
                'refresh',
                false,
            ),
            FILTER_VALIDATE_BOOLEAN,
        );

        $cacheKey = "ajaxcache:popular:$page";

        $fetchData = function () use ($page) {
            $items = Roach::collectSpider(
                GogoAjaxSpider::class,
                context: [
                    'uri' => sprintf(
                        'https://%s/ajax/page-recent-release-ongoing.html?page=%d',
                        config('app.urls.ajax'),
                        $page,
                    ),
                ],
            );
            return array_merge(
                ...array_map(
                    fn($item) => $item->all(),
                    $items,
                ),
            );
        };

        if ($refresh) {
            $result = $fetchData();
            Cache::put(
                $cacheKey,
                $result,
                now()->addHours(1),
            );
        } else {
            $result = Cache::remember(
                $cacheKey,
                now()->addHours(1),
                $fetchData,
            );
        }

        return response()->json($result);
    }
}
