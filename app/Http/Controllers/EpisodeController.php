<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpisode;
use App\Jobs\ProcessTitle;
use App\Models\Episode;

class EpisodeController extends ControllerAbstract {
    protected string $error = '';

    public function index(string $title_id) {
        $episodes = Episode::where(
            'title_id',
            $title_id,
        )->get();

        // If there are no episodes, process the Title
        if ($episodes->isEmpty()) {
            ProcessTitle::dispatchSync(
                $title_id,
                true,
            );
        }

        return response()->json(
            [
                'query'    => $title_id,
                'exists'   => $episodes->isNotEmpty(),
                'episodes' => $episodes,
            ],
        );
    }

    public function show(string $title_id, string $index) {
        $id_formats = [
            "{$title_id}-episode-{$index}",
            "{$title_id}-{$index}",
        ];

        // Try finding the episode using both formats
        $episode = Episode::whereIn(
            'id',
            $id_formats,
        )->first();

        // If it doesn't exist, process it & retry
        if (!$episode && $title_id && $index) {
            ProcessEpisode::dispatchSync(
                $id_formats[0],
                $title_id,
            );

            $episode = Episode::find($id_formats[0]);

            if (!$episode) {
                $this->error = 'Episode not found';
            }
        }

        return response()->json([
            'query'   => $title_id,
            'index'   => $index,
            'exists'  => (bool) $episode,
            'episode' => $episode,
            'errors'  => (string) $this->error ?? null
        ]);
    }
}
