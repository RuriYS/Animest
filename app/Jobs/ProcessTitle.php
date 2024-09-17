<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Bus;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

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

    public function handle(): void
    {
        $id = str($this->id);
        Log::info("Starting to process Title with ID: $id");

        try {
            $result = $this->collectAndProcessSpiderResults($id);
            if (isset($result['error'])) {
                Log::warning("Title Job with ID: '$id' discarded. Reason: {$result['error']}");
                return;
            }

            $title = $this->createOrUpdateTitle($result);
            $this->processGenres($result['genres'] ?? [], $title);
            $this->processEpisodes($result['alias'], $result['length']);

            Log::info("Title successfully processed", ['id' => $id]);
        } catch (\Exception $e) {
            Log::error("Error processing Title", ['id' => $id, 'error' => $e->getMessage()]);
        }
    }

    private function collectAndProcessSpiderResults(string $id): array
    {
        Log::debug("Collecting spider results", ['id' => $id]);

        $items = Roach::collectSpider(
            GogoSpider::class,
            context: [
                'base_url' => config('app.urls.gogo'),
                'uri' => "/category/$id"
            ]
        );

        $result = array_merge(...array_map(fn($item) => $item->all(), $items));
        Log::debug("Spider results collected", ['id' => $id, 'resultCount' => count($result)]);
        return $result;
    }

    private function createOrUpdateTitle(array $result): Title
    {
        Log::debug("Creating or updating Title", ['id' => $result['id']]);
        $titleData = array_diff_key($result, array_flip(['genres']));
        $title = Title::updateOrCreate(['id' => $result['id']], $titleData);
        Log::debug("Title created or updated", ['id' => $title->id]);
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
        Log::debug("Starting episode processing", ['alias' => $alias, 'totalEpisodes' => $length]);

        $existingEpisodes = Episode::where('title_id', $alias)
            ->pluck('episode_index')
            ->toArray();

        $jobs = [];
        for ($i = 1; $i <= $length; $i++) {
            if (!in_array($i, $existingEpisodes)) {
                $episodeId = "{$alias}-episode-{$i}";
                $jobs[] = new ProcessEpisode($episodeId);
            }
        }

        $jobCount = count($jobs);
        Log::info("Queueing episode processing jobs", ['alias' => $alias, 'jobCount' => $jobCount]);

        if (!empty($jobs)) {
            Bus::batch($jobs)
                ->progress(function (Batch $batch) use ($jobCount, $alias) {
                    $processed = $batch->processedJobs();
                    $percentage = round(($processed / $jobCount) * 100, 2);
                    Log::debug("Episode processing progress", [
                        'alias' => $alias,
                        'processed' => $processed,
                        'total' => $jobCount,
                        'percentage' => $percentage
                    ]);
                })
                ->then(function (Batch $batch) use ($length, $alias) {
                    Log::info("Episode processing completed", ['alias' => $alias, 'processedCount' => $length]);
                })
                ->catch(function (Batch $batch, \Throwable $e) use ($alias) {
                    Log::error("Error in episode processing batch", ['alias' => $alias, 'error' => $e->getMessage()]);
                })
                ->finally(function (Batch $batch) use ($alias) {
                    Log::debug("Episode processing batch finished", ['alias' => $alias, 'failedJobs' => $batch->failedJobs]);
                })
                ->dispatch();
        } else {
            Log::info("No new episodes to process", ['alias' => $alias]);
        }
    }
}
