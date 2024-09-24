<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TitleController extends ControllerAbstract {
    public function show(Request $request, string $id) {
        $process     = $request->boolean('p') ?? false;
        $refresh_eps = $request->boolean('r') ?? false;

        $title = Cache::remember("title:{$id}", 3600, function () use ($id) {
            return Title::with('genres')->find($id);
        });

        if (!$title) {
            ProcessTitle::dispatch($id, $process, $refresh_eps)->onQueue('high');
            return response()->json([
                'message' => "Title not found. Adding to queue.",
                'query'   => $id,
                'result'  => null,
            ], 202);
        }

        return response()->json([
            'errors' => null,
            'query'  => $id,
            'result' => $title->toArray(),
        ]);
    }

    public function process(Request $request, string $id) {
        $process_eps = $request->boolean('eps', true);
        $refresh_eps = $request->boolean('refresh_eps', true);

        ProcessTitle::dispatch($id, $process_eps, $refresh_eps)->onQueue('high');
        return response()->json([
            'message' => 'Job dispatched',
        ]);
    }
}
