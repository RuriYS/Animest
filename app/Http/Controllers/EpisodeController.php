<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EpisodeController extends ControllerAbstract {
    public function show(Request $request, string $title_id, int|string|null $index = 0) {
        $process = (boolean) $request->boolean('p');
        $refresh = (boolean) $request->boolean('r');

        $episodes = $this->getEpisode($request, $title_id, $process, $refresh, $index);

        return response()->json([
            'process' => $process,
            'refresh' => $refresh,
            'message' => $episodes,
        ], $process ? 202 : ($episodes ? 200 : 404));
    }

    private function getEpisode($request, $title_id, $process, $refresh, $index) {
        $key = "episode:$title_id:$index";

        if ($refresh) {
            Cache::forget($key);
        }

        if ($process) {
            app(TitleController::class)->show($request, $title_id);
            Cache::forget($key);
            return null;
        }

        $now = now();
        $ttl = now()->addHours(4);

        $payload = Cache::remember($key, $ttl, function () use ($title_id, $index, $now, $ttl) {
            $query = Episode::where('title_id', $title_id);
            $data = $index ? $query->where('episode_index', $index)->first() : $query->get();

            return [
                'result'     => $data,
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
