<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVidstreamVideoRequest;
use App\Http\Requests\UpdateVidstreamVideoRequest;
use App\Models\VidstreamVideo;
use App\Spiders\VidstreamVideoSpider;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class VidstreamVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = VidstreamVideo::all();
        return response()->json($videos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVidstreamVideoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $anime_id, int $index)
    {
        $id = "{$anime_id}-episode-{$index}";
        $video = VidstreamVideo::find($id);

        if ($video) {
            return response()->json($video);
        } else {
            return response()->json(['error' => 'Video not found'], 404);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VidstreamVideo $vidstreamVideo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVidstreamVideoRequest $request, VidstreamVideo $vidstreamVideo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VidstreamVideo $vidstreamVideo)
    {
        //
    }

    public function get(string $id)
    {
        $items = Roach::collectSpider(
            VidstreamVideoSpider::class,
            context: ['id' => $id]
        );

        $results = array_map(fn($item) => $item->all(), $items);

        $zero = $results[0] ?? null;
        if ($zero && array_key_exists('errors', $zero)) {
            return response()->json($zero);
        }

        $id = $results[1]['episode_id'];
        $meta = array_values(array_filter($results[0]['related'], function ($item) use ($id) {
            $key = key($item);
            return $key === $id;
        }));

        $video = VidstreamVideo::updateOrCreate([
            'id' => $id
        ], [
            'meta' => $meta ? $meta[0][$id] : null,
            'video' => $results[2] ?? null
        ]);

        return $video;
    }
}
