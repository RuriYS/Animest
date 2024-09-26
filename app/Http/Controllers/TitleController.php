<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TitleController extends ControllerAbstract {
    public function show(Request $request, string $id) {
        $process = (boolean) $request->boolean('p');
        $refresh = (boolean) $request->boolean('r');

        $title = $this->getTitle($id, $process, $refresh);

        return response()->json([
            'process' => $process,
            'refresh' => $refresh,
            'message' => $title,
        ], $process ? 202 : ($title ? 200 : 404));
    }

    private function getTitle($id, $process, $refresh) {
        $key = "title:$id";

        if ($refresh) {
            Cache::forget($key);
        }

        if ($process) {
            ProcessTitle::dispatch($id, true, $refresh)->onQueue('high') ? ['result' => null, 'status' => 'dispatched'] : null;
            Cache::forget($key);
            return null;
        }

        $now = now();
        $ttl = now()->addHours(4);

        $payload = Cache::remember($key, $ttl, function () use ($id, $now, $ttl) {
            $result = Title::with('genres')->find($id);
            if (!$result) {
                ProcessTitle::dispatchSync($id);
                $result = Title::with('genres')->find($id);
            }
            return [
                'result'     => $result,
                'created_at' => $now,
                'ttl'        => floor($now->diffInSeconds($ttl)),
            ];
        });

        $age = floor($payload['created_at']->diffInSeconds(now()));
        unset($payload['created_at']);

        return [
            ...$payload,
            'age' => $age,
        ];
    }
}
