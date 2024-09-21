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

    protected string $id;
    protected string $titleId;

    public function __construct(string $id, string $titleId) {
        $this->id      = $id;
        $this->titleId = $titleId;
    }

    public function uniqueId(): string {
        return "{$this->id}_{$this->titleId}";
    }

    public function uniqueFor(): int {
        return 3600;
    }

    public function handle() {
        Log::debug('Processing Episode', ['id' => $this->id, 'title_id' => $this->titleId]);

        $results = $this->collectSpiderResults();

        if (isset($results['error'])) {
            Log::warning('Episode Job discarded', ['id' => $this->id, 'reason' => $results['error']]);
            // throw new Exception("{$results['error']}", 1);
        }

        $episodeData = $this->processResults($results);

        $this->ensureTitleExists($this->titleId);
        $this->createOrUpdateEpisode($episodeData);
    }

    private function collectSpiderResults(): array {
        $items   = Roach::collectSpider(VidstreamVideoSpider::class, context: [
            'base_url' => config('app.urls.vidstream'),
            'id'       => $this->id,
        ]);
        $results = array_merge(...array_map(fn($item) => $item->all(), $items));

        Log::debug('Spider collected', [
            'episode_id' => $this->id,
            'results'    => $results,
        ]);

        return $results;
    }

    private function processResults(array $results): array {
        $episodeId = trim($results['episode_id'] ?? '');
        $titleId   = $this->titleId;

        $episodes = $results['episodes'] ?? [];
        $meta     = $this->findEpisodeMeta($episodes, $episodeId);

        if (!$episodeId || !$titleId) {
            Log::warning('Invalid episode or title ID', ['episode_id' => $episodeId, 'title_id' => $titleId]);
            // throw new Exception("Invalid episode or title ID");
        }

        Log::debug('Results processed', ['episodeId' => $episodeId, 'titleId' => $titleId]);

        return [
            'id'            => $episodeId,
            'episode_index' => explode('episode-', $episodeId)[1] ?? null,
            'download_url'  => $results['download_url'] ?? null,
            'title_id'      => $titleId,
            'upload_date'   => $meta['date_added'] ?? null,
            'video'         => $results['stream_data'] ?? null,
        ];
    }

    private function findEpisodeMeta(array $episodes, string $episodeId): ?array {
        $filtered = array_values(array_filter($episodes, function ($item) use ($episodeId) {
            return $item['episode_id'] == $episodeId;
        }));
        return $filtered ? $filtered[0] : null;
    }

    private function ensureTitleExists(string $titleId): void {
        if (!Title::where('id', $titleId)->exists()) {
            Log::debug('Title not found, creating new Title', ['titleId' => $titleId]);
            ProcessTitle::dispatchSync($titleId);
        }
    }

    private function createOrUpdateEpisode(array $episodeData): void {
        $episode = Episode::updateOrCreate(
            ['id' => $episodeData['id']],
            $episodeData,
        );
        Log::debug('Episode updated', ['id' => $episode->id]);
    }
}
