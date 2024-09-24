<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TitleController extends ControllerAbstract {
    public function show(Request $request, string $id) {
        $process = $request->boolean('p') ?? false;
        $refresh = $request->boolean('r') ?? false;

        $title = Cache::remember("title:{$id}", 3600, function () use ($id) {
            return Title::with('genres')->find($id);
        });

        if (!$title) {
            $result = null;
            if ($process) {
                ProcessTitle::dispatch($id, $process, $refresh)->onQueue('high');
            } else {
                ProcessTitle::dispatchSync($id, $process, false);
                $result = Title::with('genres')->find($id);
            }
            return response()->json([
                'message' => $process ? 'Title not found. Adding to queue.' : ($result ? 'Success' : 'Not found'),
                'query'   => $id,
                'result'  => $result,
            ], $process ? 202 : ($result ? 200 : 404));
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
