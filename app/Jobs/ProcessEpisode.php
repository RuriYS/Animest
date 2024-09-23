<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Title;
use App\Spiders\VidstreamVideoSpider;
use App\Utils\Helper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessEpisode implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $episode_id;
    protected string $title_id;

    public function __construct(string $episode_id, string $title_id) {
        $this->episode_id = $episode_id;
        $this->title_id   = $title_id;
    }

    public function uniqueId(): string {
        return md5("{$this->episode_id}_{$this->title_id}");
    }

    public function uniqueFor(): int {
        return 3600;
    }

    public function handle() {
        Log::debug('[ProcessEpisode] Job started', [
            'episode_id' => $this->episode_id,
            'title_id'   => $this->title_id,
        ]);

        try {
            $results = $this->runSpider();

            if (isset($results['error'])) {
                Log::warning('[ProcessEpisode] Job rejected', [
                    'id'     => $this->episode_id,
                    'reason' => $results['error'],
                ]);
                return;
            }

            DB::transaction(function () use ($results) {
                $this->createOrUpdateTitle($this->title_id);
                $this->createOrUpdateEpisode($results);
            });

        } catch (Exception $e) {
            Log::error('[ProcessEpisode] Job failed', [
                'episode_id' => $this->episode_id,
                'title_id'   => $this->title_id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function runSpider(): array {
        $items = Roach::collectSpider(
            VidstreamVideoSpider::class,
            context: [
                'base_url' => config('app.urls.vidstream'),
                'id'       => $this->episode_id,
            ],
        );

        return array_merge(...array_map(fn($item) => $item->all(), $items));
    }

    private function createOrUpdateTitle(string $titleId): void {
        if (!Title::where('id', $titleId)->exists()) {
            Log::debug('[ProcessEpisode] Creating title', ['title_id' => $titleId]);
            ProcessTitle::dispatchSync($titleId);
        }
    }

    private function createOrUpdateEpisode(array $results): void {
        $episodeData = $this->prepareEpisodeData($results);

        $episode = Episode::updateOrCreate(
            ['id' => $episodeData['id']],
            $episodeData,
        );

        $this->refreshEpisodeCache($episode);
        $this->refreshEpisodesListCache();

        Log::debug('[ProcessEpisode] Episode updated', [$episodeData]);
    }

    private function prepareEpisodeData(array $results): array {
        $id_fragments = Helper::parseEpisodeID($results['episode_id'] ?? '');
        $meta         = $this->findEpisodeMeta($results['episodes'] ?? [], $results['episode_id'] ?? '');

        return [
            'id'            => $results['episode_id'] ?? '',
            'alias'         => $id_fragments['alias'] ?? '',
            'episode_index' => $id_fragments['index'] ?? null,
            'download_url'  => $results['download_url'] ?? null,
            'title_id'      => $this->title_id,
            'upload_date'   => $meta['date_added'] ?? null,
            'video'         => $results['stream_data'] ?? null,
        ];
    }

    private function findEpisodeMeta(array $episodes, string $episodeId): ?array {
        return collect($episodes)->firstWhere('episode_id', $episodeId) ?? null;
    }

    private function refreshEpisodeCache(Episode $episode): void {
        Cache::put("episode:{$episode->id}", $episode, 3600);
        Log::debug('[ProcessEpisode] Episode cache refreshed', ['episode_id' => $episode->id]);
    }

    private function refreshEpisodesListCache(): void {
        $episodes = Episode::where('title_id', $this->title_id)->get();
        Cache::put("episodes:{$this->title_id}", $episodes, 3600);
        Log::debug('[ProcessEpisode] Episodes list cache refreshed', ['title_id' => $this->title_id, 'episode_count' => $episodes->count()]);
    }
}
