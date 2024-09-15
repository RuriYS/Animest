<?php

namespace App\Jobs;

use App\Models\Title;
use App\Models\Episode;
use App\Spiders\VidstreamVideoSpider;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessEpisode implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public $uniqueFor = 3600;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function uniqueId(): string
    {
        return $this->id;
    }

    public function handle()
    {
        $items = Roach::collectSpider(
            VidstreamVideoSpider::class,
            context: ['id' => $this->id]
        );

        $results = array_merge(...array_map(fn($item) => $item->all(), $items));

        if (isset($results['error'])) {
            Log::warning("Episode Job with ID: '$this->id' has been discarded with reason: {$results['error']}");
            return;
        }

        $episodes = $results['episodes'] ?? null;

        $episode_id = trim($results['episode_id']) ?? null;
        $titleId = explode('-episode', $episode_id)[0] ?? null;
        $meta = array_values(array_filter($episodes, function ($item) use ($episode_id) {
            return $item['episode_id'] == $episode_id;
        }));

        $meta = $meta ? $meta[0] : null;

        $titleExists = Title::where('id', $titleId)->exists();

        if (!$titleExists) {
            Log::info("Attempting to create a Title for the episode with title_id: $titleId");
            ProcessTitle::dispatchSync($titleId);
        }

        Episode::updateOrCreate([
            'id' => $episode_id
        ], [
            'episode_index' => explode('episode-', $episode_id)[1],
            'download_url' => $results['download_url'],
            'title_id' => $titleId,
            'upload_date' => $meta['date_added'],
            'video' => $results['stream_data'],
        ]);

        Log::info("Episode job finished");
    }
}
