<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;

class AnimeController extends Controller
{
    public function index()
    {
        $animes = Anime::all();

        return response()->json([
            'count' => $animes->count(),
            'animes' => $animes
        ]);
    }

    public function show(string $id)
    {
        $anime = Anime::find($id);
        $episodes = Episode::where('title_id', $id)->count();

        return response()->json([
            'query' => $id,
            'exists' => !!$anime,
            'id' => $id,
            'title' => $anime->title,
            'description' => $anime->description,
            'episodes' => $episodes,
            'splash' => $anime->splash,
        ]);
    }
}
