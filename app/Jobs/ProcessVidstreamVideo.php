<?php

namespace App\Jobs;

use App\Models\VidstreamVideo;
use App\Spiders\VidstreamVideoSpider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RoachPHP\Roach;

class ProcessVidstreamVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * Create a new job instance.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }


    /**
     * Execute the job.
     */
    public function handle()
    {
        $items = Roach::collectSpider(
            VidstreamVideoSpider::class,
            context: ['id' => $this->id]
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
