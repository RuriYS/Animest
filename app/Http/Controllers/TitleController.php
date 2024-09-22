<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use Illuminate\Http\Request;

class TitleController extends ControllerAbstract {
    public function show(string $id) {
        $title = Title::with('genres')->find($id);

        if (!$title) {
            ProcessTitle::dispatchSync($id, false);
            $title = Title::with('genres')->find($id);
        }

        return response()->json([
            'errors' => $title ? null : "Title not found, it's either invalid or doesn't exist",
            'query'  => $id,
            'result' => $title?->toArray(),
        ]);
    }

    public function process(Request $request, string $id) {
        $process_eps = $request->boolean('eps', false);
        $refresh_eps = $request->boolean('refresh_eps', true);

        ProcessTitle::dispatch($id, $process_eps, $refresh_eps);
        return response()->json([
            'message' => 'Job dispatched',
        ]);
    }
}
