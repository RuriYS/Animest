<?php

namespace App\Http\Controllers;

use App\Jobs\GetVideo;
use App\Models\Episode;

class VideoController extends Controller
{
    public function index(string $anime_id)
    {

    }

    public function show(string $anime_id, int $index)
    {
        $id = "{$anime_id}-episode-{$index}";
        $video = Episode::find($id);

        if ($video) {
            return response()->json($video);
        } else {
            return response()->json(['error' => 'Video not found'], 404);
        }
    }

    public function get(string $id)
    {
        GetVideo::dispatch($id);
        return response()->noContent(200);
    }
}
