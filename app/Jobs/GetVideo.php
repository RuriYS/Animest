<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Episode;
use App\Spiders\VidstreamVideoSpider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use RoachPHP\Roach;

class GetVideo implements ShouldQueue
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

        Log::debug(print_r($results, true));
        if (count($results) < 4) {
            return;
        }

        $anime_details = $results[0] ?? null;
        $episodes = $results[1] ?? null;
        $episode = $results[2] ?? null;
        $source = $results[3] ?? null;

        $id = $episode['episode_id'] ?? null;

        $meta = array_values(array_filter($episodes, function ($item) use ($id) {
            return $item['episode_id'] == $id;
        }))[0];

        Episode::updateOrCreate([
            'id' => $id
        ], [
            'episode_index' => explode('episode-', $id)[1] ?? null,
            'upload_date' => $meta['date_added'] ?? null,
            'video' => $source ?? null,
        ]);

        Anime::updateOrCreate([
            'id' => explode('-episode', $id)[0],
            'title' => $anime_details['title'] ?? null,
            'description' => $anime_details['description'] ?? null,
        ], []);

        Log::info("Episode saved (ID: $id)");
        return 0;
    }
}
