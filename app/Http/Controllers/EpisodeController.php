<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpisode;
use App\Jobs\ProcessTitle;
use App\Models\Episode;

class EpisodeController extends Controller
{
    public function index(string $title_id)
    {
        $episodes = Episode::where('title_id', $title_id)->get();

        if ($episodes->isEmpty()) {
            ProcessTitle::dispatchSync($title_id, true);
        }

        return response()->json([
            'query' => $title_id,
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

    public function view(string $anime_id, string $index)
    {
        $episodeIdFormats = [
            "{$anime_id}-episode-{$index}",
            "{$anime_id}-{$index}"
        ];

        $episode = Episode::find($episodeIdFormats[0]) ?? Episode::find($episodeIdFormats[1]);
        $episode->save();
        return response($episode->views, 200);
    }

    public function process(string $anime_id, string $index)
    {
        $id = "$anime_id-episode-$index";
        ProcessEpisode::dispatch($id);
        return response()->json([
            'query' => $id,
            'message' => 'Job dispatched'
        ]);
    }
}
