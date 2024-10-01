<?php

namespace App\Jobs;

use App\Events\EpisodeProcessed;
use App\Models\Episode;
use App\Models\Title;
use App\Spiders\VidstreamSpider;
use App\Utils\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
                $episode = $this->updateEpisode($results);

                if ($episode) {
                    event(new EpisodeProcessed($episode));
                    Log::debug('[ProcessEpisode] Job finished', ['episode' => $episode]);
                }
            });

        } catch (\Exception $e) {
            Log::error('[ProcessEpisode] Job failed', [
                'episode_id' => $this->episode_id,
                'title_id'   => $this->title_id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function runSpider(): array {
        $items = Roach::collectSpider(
            VidstreamSpider::class,
            context: [
                'base_url' => config('app.urls.vidstream'),
                'id'       => $this->episode_id,
            ],
        );

        return array_merge(...array_map(fn($item) => $item->all(), $items));
    }

    private function updateEpisode(array $results) {
        try {
            $data = $this->prepareEpisode($results);

            if (!isset($data['id'])) {
                throw new \Exception('Missing ID for episode: ' . json_encode($data));
            }

            $episode = Episode::updateOrCreate(
                ['id' => $data['id']],
                $data,
            );

            return $episode;
        } catch (\Throwable $th) {
            Log::error('[ProcessEpisode] Failed to update episode', ['error' => $th->getMessage()]);
        }
    }

    private function prepareEpisode(array $results): array {
        $id_fragments = Helper::parseEpisodeID($results['episode_id'] ?? '');
        $meta         = $this->findEpisodeMeta($results['episodes'] ?? [], $results['episode_id'] ?? '');

        $data = [
            'id'            => $results['episode_id'],
            'alias'         => $id_fragments['alias'],
            'episode_index' => $id_fragments['index'],
            'download_url'  => $results['download_url'] ?? null,
            'title_id'      => $this->title_id,
            'upload_date'   => $meta['date_added'] ?? null,
            'video'         => $results['stream_data'] ?? null,
        ];

        return $data;
    }

    private function findEpisodeMeta(array $episodes, string $episodeId): ?array {
        return collect($episodes)->firstWhere('episode_id', $episodeId) ?? null;
    }
}
