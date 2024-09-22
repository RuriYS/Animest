<?php

namespace App\Jobs;

use App\Models\Title;
use App\Models\Episode;
use App\Spiders\VidstreamVideoSpider;
use App\Utils\CateParser;
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

    protected string $alias;

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
            '[ProcessEpisode] Processing Episode',
            [
                'episode_id' => $this->episode_id,
                'title_id'   => $this->title_id,
            ],
        );

        $this->runSpider();

        if (isset($this->results['error'])) {
            Log::warning(
                '[ProcessEpisode] Episode Job discarded',
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
            '[ProcessEpisode] Spider collected',
            [
                'episode_id' => $this->episode_id,
                'results'    => $this->results,
            ],
        );
    }

    private function processResults(array $results) {
        $episode_id = trim($results['episode_id'] ?? '');
        $title_id   = $this->title_id;

        $episodes = $results['episodes'] ?? [];
        $meta     = $this->findEpisodeMeta($episodes, $episode_id);

        if (!$episode_id || !$title_id) {
            Log::warning('Invalid episode or title ID', [
                'episode_id' => $episode_id,
                'title_id'   => $title_id,
            ]);
        }

        $id_fragments = CateParser::parseEpisodeID($episode_id);
        $this->alias  = $id_fragments['alias'];

        Log::debug('[ProcessEpisode] Results processed', [
            'episode_id' => $episode_id,
            'title_id'   => $title_id,
        ]);

        $this->episode_data = [
            'id'            => $episode_id,
            'alias'         => $this->alias,
            'episode_index' => $id_fragments['index'],
            'download_url'  => $results['download_url'] ?? null,
            'title_id'      => $title_id,
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
                '[ProcessEpisode] Creating title',
                ['title_id' => $titleId],
            );
            ProcessTitle::dispatchSync($titleId);
        }
    }

    private function createEpisode(array $episode_data): void {
        $episode = Episode::updateOrCreate(
            ['id' => $episode_data['id']],
            $episode_data,
        );

        Log::debug(
            '[ProcessEpisode] Episode updated',
            [
                $episode_data,
            ],
        );
    }
}
