<?php

namespace App\Listeners;

use App\Events\EpisodeProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EpisodeListener implements ShouldQueue {
    public function handle(EpisodeProcessed $event): void {
        $episode      = $event->episode;
        $titleId      = $episode->title_id;
        $episodeIndex = $episode->episode_index;

        if ($episode) {
            Log::debug('[EpisodeListener] New episode', [$episode->toArray()]);
            $key  = "episode:$titleId:$episodeIndex";
            $data = [
                'created_at' => now(),
                'result'     => $episode,
                'status'     => true,
                'ttl'        => floor(now()->diffInSeconds(now()->addHours(4))),
            ];

            Cache::put($key, $data);
            Log::debug("[EpisodeListener] Cache updated", ['key' => $key]);
        }
    }
}
