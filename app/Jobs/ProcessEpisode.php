<?php

namespace App\Jobs;

use App\Models\Title;
use App\Models\Episode;
use App\Spiders\VidstreamVideoSpider;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessEpisode implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $id;
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
        Log::info("Starting to process Episode", ['id' => $this->id]);

        try {
            $results = $this->collectSpiderResults();

            if (isset($results['error'])) {
                Log::warning("Episode Job discarded", ['id' => $this->id, 'reason' => $results['error']]);
                return;
            }

            $episodeData = $this->processResults($results);
            $this->ensureTitleExists($episodeData['title_id']);
            $this->createOrUpdateEpisode($episodeData);

            Log::info("Episode successfully processed", ['id' => $this->id]);
        } catch (\Exception $e) {
            Log::error("Error processing Episode", ['id' => $this->id, 'error' => $e->getMessage()]);
        }
    }

    private function collectSpiderResults(): array
    {
        Log::debug("Collecting spider results", ['id' => $this->id]);
        $items = Roach::collectSpider(VidstreamVideoSpider::class, context: ['id' => $this->id]);
        $results = array_merge(...array_map(fn($item) => $item->all(), $items));
        Log::debug("Spider results collected", ['id' => $this->id, 'resultCount' => count($results)]);
        return $results;
    }

    private function processResults(array $results): array
    {
        Log::debug("Processing spider results", ['id' => $this->id]);

        $episodeId = trim($results['episode_id'] ?? '');
        $titleId = explode('-episode', $episodeId)[0] ?? null;

        $episodes = $results['episodes'] ?? [];
        $meta = $this->findEpisodeMeta($episodes, $episodeId);

        if (!$episodeId || !$titleId) {
            throw new \Exception("Invalid episode or title ID");
        }

        Log::debug("Results processed", ['episodeId' => $episodeId, 'titleId' => $titleId]);

        return [
            'id' => $episodeId,
            'episode_index' => explode('episode-', $episodeId)[1] ?? null,
            'download_url' => $results['download_url'] ?? null,
            'title_id' => $titleId,
            'upload_date' => $meta['date_added'] ?? null,
            'video' => $results['stream_data'] ?? null,
        ];
    }

    private function findEpisodeMeta(array $episodes, string $episodeId): ?array
    {
        $filtered = array_values(array_filter($episodes, function ($item) use ($episodeId) {
            return $item['episode_id'] == $episodeId;
        }));
        return $filtered ? $filtered[0] : null;
    }

    private function ensureTitleExists(string $titleId): void
    {
        Log::debug("Checking if Title exists", ['titleId' => $titleId]);
        if (!Title::where('id', $titleId)->exists()) {
            Log::info("Title not found, creating new Title", ['titleId' => $titleId]);
            ProcessTitle::dispatchSync($titleId);
        }
    }

    private function createOrUpdateEpisode(array $episodeData): void
    {
        Log::debug("Creating or updating Episode", ['id' => $episodeData['id']]);
        $episode = Episode::updateOrCreate(
            ['id' => $episodeData['id']],
            $episodeData
        );
        Log::debug("Episode created or updated", ['id' => $episode->id]);
    }
}
