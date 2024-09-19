<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected string $id;
    protected bool $processEps;
    public $uniqueFor = 3600;

    public function __construct(string $id, bool $processEps = true)
    {
        $this->id = $id;
        $this->processEps = $processEps;
    }

    public function uniqueId(): string
    {
        return $this->id;
    }

    public function handle(): void
    {
        $id = str($this->id)->toString();
        Log::debug('Processing Title', ['id' => $id]);

        try {
            $result = $this->collectSpider($id);

            if (isset($result['error'])) {
                Log::warning('Title Job discarded', ['id' => $this->id, 'reason' => $result['error']]);
                return;
            }

            $title = $this->updateTitle($result);
            $this->processGenres($result['genres'] ?? [], $title);

            if ($this->processEps) {
                $this->processEpisodes($result['alias'], $result['length']);
            }

        } catch (\Exception $e) {
            Log::error('Title processing failed', ['id' => $id, 'error' => $e->getMessage()]);
        }
    }

    private function collectSpider(string $id): array
    {
        $items = Roach::collectSpider(
            GogoSpider::class,
            context: [
                'uri' => 'https://' . config('app.urls.gogo') . "/category/$id"
            ]
        );

        $result = array_merge(...array_map(fn($item) => $item->all(), $items));
        Log::debug('Spider collected', ['id' => $id, 'resultCount' => count($result)]);
        return $result;
    }

    private function updateTitle(array $result): Title
    {
        $titleData = array_diff_key($result, array_flip(['genres']));
        $title = Title::updateOrCreate(['id' => $result['id']], $titleData);

        Log::debug('Title updated', ['id' => $title->id]);
        return $title;
    }

    private function processGenres(array $genres, Title $title): void
    {
        $genres = array_map('trim', $genres);
        Log::debug("Processing genres", ['titleId' => $title->id, 'genreCount' => count($genres)]);

        $genreIds = Genre::whereIn('name', $genres)->pluck('id')->toArray();

        if (!empty($genreIds)) {
            try {
                $title->genres()->sync($genreIds);
                Log::debug("Genres synced", ['titleId' => $title->id, 'genreCount' => count($genreIds)]);
            } catch (\Exception $e) {
                Log::error("Error syncing genres", ['titleId' => $title->id, 'error' => $e->getMessage()]);
            }
        } else {
            Log::warning("No valid genres found", ['titleId' => $title->id]);
        }
    }

    private function processEpisodes(string $alias, int $length): void
    {
        $existingEpisodes = Episode::where('title_id', $alias)
            ->pluck('episode_index')
            ->toArray();

        $titleId = $this->id;
        $jobs = [];

        for ($i = 1; $i <= $length; $i++) {
            if (!in_array($i, $existingEpisodes)) {
                $episodeId = "{$alias}-episode-{$i}";
                $jobs[] = new ProcessEpisode($episodeId, $titleId);
            }
        }

        $jobCount = count($jobs);

        if (!empty($jobs)) {
            Log::debug("Dispatching episode jobs", ['alias' => $alias, 'totalEpisodes' => $length]);
            Bus::batch($jobs)
                ->progress(function (Batch $batch) use ($jobCount, $alias) {
                    $processed = $batch->processedJobs();
                    $percentage = round(($processed / $jobCount) * 100, 2);

                    Log::debug("Episode processing", [
                        'alias' => $alias,
                        'processed' => $processed,
                        'total' => $jobCount,
                        'percentage' => $percentage
                    ]);
                })
                ->then(function (Batch $batch) use ($length, $alias) {
                    Log::debug("Episode completed", ['alias' => $alias, 'processedCount' => $length]);
                })
                ->catch(function (Batch $batch, \Throwable $e) use ($alias) {
                    Log::error("Episode batch error", ['alias' => $alias, 'error' => $e->getMessage()]);
                })
                ->finally(function (Batch $batch) use ($alias) {
                    Log::debug("Episode batch finished", ['alias' => $alias, 'failedJobs' => $batch->failedJobs]);
                })
                ->dispatch();
        } else {
            Log::debug("No new episodes to dispatch", ['alias' => $alias]);
        }
    }
}
