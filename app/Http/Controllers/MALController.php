<?php

namespace App\Http\Controllers;

use App\Spiders\MALSpider;
use Illuminate\Http\Request;
use RoachPHP\Roach;

class MALController {
    public function search(Request $request) {
        $query = $request->input('q');
        $items = Roach::collectSpider(
            spiderClass: MALSpider::class,
            context: ['action' => 'search', 'keyword' => $query],
        );

        $result = array_merge(...array_map(
            function ($item) {
                return $item->all();
            }, $items
        ));

        return response()->json([
            'result' => $result
        ]);
    }
}
