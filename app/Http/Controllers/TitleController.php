<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;

class TitleController extends Controller
{
    public function show(string $id)
    {
        $title = Title::find($id);

        if (!$title) {
            ProcessTitle::dispatchSync($id, false);
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
}
