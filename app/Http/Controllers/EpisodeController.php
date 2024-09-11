<?php

namespace App\Http\Controllers;

use App\Jobs\GetVideo;
use App\Models\Episode;

class EpisodeController extends Controller
{
    public function index(string $anime_id)
    {
        $episodes = Episode::where('title_id', $anime_id)->get();
        return response()->json([
            'query' => $anime_id,
            'exists' => $episodes->isNotEmpty(),
            'episodes' => $episodes
        ]);
    }

    public function show(string $anime_id, string $index)
    {
        $episodeIdFormats = [
            "{$anime_id}-episode-{$index}",
            "{$anime_id}-{$index}"
        ];

        $episode = Episode::find($episodeIdFormats[0]) ?? Episode::find($episodeIdFormats[1]);

        return response()->json([
            'query' => $anime_id,
            'index' => $index,
            'exists' => !!$episode,
            'episode' => $episode
        ]);
    }

    public function get(string $id)
    {
        GetVideo::dispatch($id);
        return response()->noContent(200);
    }
}
