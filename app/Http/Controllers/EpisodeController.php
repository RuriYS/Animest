<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpisode;
use App\Jobs\ProcessTitle;
use App\Models\Episode;

class EpisodeController extends Controller {
    protected string $error = '';

    public function index(string $title_id) {
        $episodes = Episode::where('title_id', $title_id)->get();

        if ($episodes->isEmpty()) {
            ProcessTitle::dispatchSync($title_id, true);
        }

        return response()->json([
            'query'    => $title_id,
            'exists'   => $episodes->isNotEmpty(),
            'episodes' => $episodes,
        ]);
    }

    public function show(string $title_id, string $index) {
        $episodeIdFormats = [
            "{$title_id}-episode-{$index}",
            "{$title_id}-{$index}",
        ];

        // Try finding the episode using both formats
        $episode = Episode::whereIn('id', $episodeIdFormats)->first();

        // If episode doesn't exist, process it and retry fetching
        if (!$episode && $title_id && $index) {
            ProcessEpisode::dispatchSync($episodeIdFormats[0], $title_id);
            $episode = Episode::find($episodeIdFormats[0]);

            if (!$episode) {
                $this->error = 'Episode not found';
            }
        }

        return response()->json([
            'query'   => $title_id,
            'index'   => $index,
            'exists'  => (bool) $episode,
            'episode' => $episode,
            'errors'  => $this->error ?? null
        ]);
    }

    public function view(string $anime_id, string $index) {
        $episodeIdFormats = [
            "{$anime_id}-episode-{$index}",
            "{$anime_id}-{$index}",
        ];

        $episode = Episode::find($episodeIdFormats[0]) ?? Episode::find($episodeIdFormats[1]);
        $episode->save();
        return response($episode->views, 200);
    }

    // public function process(string $anime_id, string $index)
    // {
    //     $id = "$anime_id-episode-$index";
    //     ProcessEpisode::dispatch($id);
    //     return response()->json([
    //         'query' => $id,
    //         'message' => 'Job dispatched'
    //     ]);
    // }
}
