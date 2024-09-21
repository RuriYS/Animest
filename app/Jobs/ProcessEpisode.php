<?php

namespace App\Jobs;

use App\Models\Title;
use App\Models\Episode;
use App\Spiders\VidstreamVideoSpider;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessEpisode implements ShouldQueue, ShouldBeUnique {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $episode_id;

    protected string $title_id;

    protected array $results;

    protected array $episode_data;

    public function __construct(string $episode_id, string $title_id) {
        $this->episode_id = $episode_id;
        $this->title_id   = $title_id;
    }

    public function uniqueId(): string {
        return "{$this->episode_id}_{$this->title_id}";
    }

    public function uniqueFor(): int {
        return 3600;
    }

    public function handle() {
        Log::debug(
            'Processing Episode',
            [
                'id'       => $this->episode_id,
                'title_id' => $this->title_id,
            ],
        );

        $this->runSpider();

        if (isset($this->results['error'])) {
            Log::warning(
                'Episode Job discarded',
                [
                    'id'     => $this->episode_id,
                    'reason' => $this->results['error'],
                ],
            );
        }

        $this->processResults(
            $this->results,
        );
        $this->createTitle(
            $this->title_id,
        );
        $this->createEpisode(
            $this->episode_data,
        );
    }

    private function runSpider() {
        $items = Roach::collectSpider(
            VidstreamVideoSpider::class,
            context: [
                'base_url' => config('app.urls.vidstream'),
                'id'       => $this->episode_id,
            ],
        );

        $this->results = array_merge(
            ...array_map(
                fn($item) => $item->all(),
                $items,
            ),
        );

        Log::debug(
            'Spider collected',
            [
                'episode_id' => $this->episode_id,
                'results'    => $this->results,
            ],
        );
    }

    private function processResults(array $results) {
        $episodeId = trim($results['episode_id'] ?? '');
        $titleId   = $this->title_id;

        $episodes = $results['episodes'] ?? [];
        $meta     = $this->findEpisodeMeta($episodes, $episodeId);

        if (!$episodeId || !$titleId) {
            Log::warning('Invalid episode or title ID', [
                'episode_id' => $episodeId,
                'title_id'   => $titleId,
            ]);
        }

        Log::debug('Results processed', [
            'episodeId' => $episodeId,
            'titleId'   => $titleId,
        ]);

        $this->episode_data = [
            'id'            => $episodeId,
            'episode_index' => explode('episode-', $episodeId)[1] ?? null,
            'download_url'  => $results['download_url'] ?? null,
            'title_id'      => $titleId,
            'upload_date'   => $meta['date_added'] ?? null,
            'video'         => $results['stream_data'] ?? null,
        ];
    }

    private function findEpisodeMeta(array $episodes, string $episodeId): ?array {
        $filtered = array_values(
            array_filter(
                $episodes,
                function ($item) use ($episodeId) {
                    return $item['episode_id'] == $episodeId;
                }
            ),
        );
        return $filtered ? $filtered[0] : null;
    }

    private function createTitle(string $titleId): void {
        if (!Title::where(
            'id',
            $titleId,
        )->exists()) {
            Log::debug(
                'Creating title',
                ['titleId' => $titleId],
            );
            ProcessTitle::dispatchSync($titleId);
        }
    }

    private function createEpisode(array $episodeData): void {
        $episode = Episode::updateOrCreate(
            ['id' => $episodeData['id']],
            $episodeData,
        );
        Log::debug(
            'Episode updated',
            ['id' => $episode->episode_id],
        );
    }
}
