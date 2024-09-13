<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use App\Models\Episode;

class TitleController extends Controller
{
    public function index()
    {
        $titles = Title::all();

        return response()->json([
            'count' => $titles->count(),
            'titles' => $titles
        ]);
    }

    public function show(string $id)
    {
        $title = Title::find($id);

        if (!$title) {
            ProcessTitle::dispatchSync($id);
            $title = Title::find($id);
        }

        return response()->json([
            'endpoint' => 'titles',
            'query' => $id,
            'result' => $title?->toArray()
        ]);
    }
}
