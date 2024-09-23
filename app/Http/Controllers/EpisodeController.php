<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpisode;
use App\Jobs\ProcessTitle;
use App\Models\Episode;
use App\Models\Title;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EpisodeController extends ControllerAbstract {
    public function index(Request $request, string $title_id) {
        $title = Title::find($title_id);

        if (!$title || $request->boolean('refresh')) {
            ProcessTitle::dispatch($title_id)->onQueue('high');
            return response()->json([
                'query'    => $title_id,
                'exists'   => false,
                'episodes' => [],
                'message'  => 'Title processing initiated. Please try again shortly.',
            ], 202);
        }

        $episodes = Cache::remember("episodes:{$title_id}", 3600, function () use ($title_id) {
            return Episode::where('title_id', $title_id)->get();
        });

        if ($episodes->isEmpty() || $episodes->count() < $title->length) {
            ProcessTitle::dispatch($title_id, true, false)->onQueue('high');
            return response()->json([
                'query'    => $title_id,
                'exists'   => true,
                'episodes' => $episodes,
                'message'  => 'Episode list may be incomplete. Processing initiated.',
            ], 202);
        }

        return response()->json([
            'query'    => $title_id,
            'exists'   => true,
            'episodes' => $episodes,
        ]);
    }

    public function show(string $title_id, string $index) {
        $id_formats = [
            "{$title_id}-episode-{$index}",
            "{$title_id}-{$index}",
        ];

        $episode = Cache::remember("episode:{$id_formats[0]}", 3600, function () use ($id_formats) {
            return Episode::whereIn('id', $id_formats)->first();
        });

        if (!$episode && $title_id && $index) {
            ProcessEpisode::dispatch($id_formats[0], $title_id)->onQueue('high');
            return response()->json([
                'query'   => $title_id,
                'index'   => $index,
                'exists'  => false,
                'episode' => null,
                'message' => 'Episode processing initiated. Please try again shortly.',
            ], 202);
        }

        return response()->json([
            'query'   => $title_id,
            'index'   => $index,
            'exists'  => (bool) $episode,
            'episode' => $episode,
        ]);
    }
}
