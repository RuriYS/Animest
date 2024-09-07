<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVidstreamVideoRequest;
use App\Http\Requests\UpdateVidstreamVideoRequest;
use App\Jobs\ProcessVidstreamVideo;
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
        ProcessVidstreamVideo::dispatch($id);

        return response()->noContent(200);
    }
}
