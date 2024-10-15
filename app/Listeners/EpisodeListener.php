<?php

namespace App\Listeners;

use App\Events\EpisodeProcessed;
use App\Utils\CacheManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class EpisodeListener implements ShouldQueue {
    public function handle(EpisodeProcessed $event): void {
        $episode      = $event->episode;
        $titleId      = $episode->title_id;
        $episodeIndex = $episode->episode_index;

        if ($episode) {
            Log::debug('[EpisodeListener] New episode', [$episode->toArray()]);

            $singleKey = "episode:$titleId:$episodeIndex";
            CacheManager::updateMediaCache($singleKey, $episode);
            Log::debug("[EpisodeListener] Episode cache updated", ['key' => $singleKey]);

            $listKey     = "episodes:$titleId";
            $episodeList = $episode->where('title_id', $titleId)->paginate(100, ['*'], 'p', 1);
            CacheManager::updateMediaCache($listKey, $episodeList);
            Log::debug("[EpisodeListener] Episode list cache updated", ['key' => $listKey]);
        }
    }
}
