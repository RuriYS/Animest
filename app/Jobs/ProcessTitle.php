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
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        Log::debug("Processing Title with ID: $this->id");

        $items = Roach::collectSpider(
            GogoSpider::class,
            context: ['id' => $this->id]
        );

        $result = array_merge(
            ...array_map(fn($item) => $item->all(), $items)
        );

        if (isset($result['error'])) {
            Log::warning("Title Job with ID: '$this->id' has been discarded with reason: {$result['error']}");
            return;
        }

        $genres = array_map('trim', $result['genres']);
        unset($result['genres']);

        $title = Title::updateOrCreate(
            ['id' => $result['id']],
            $result
        );

        $genreIDs = [];
        foreach ($genres as $genreName) {
            $genre = Genre::where('name', $genreName)->first();
            if ($genre) {
                $genreIDs[] = $genre->id;
            } else {
                Log::warning("Genre not found: {$genreName}");
            }
        }

        if (!empty($genreIDs)) {
            try {
                $title->genres()->sync($genreIDs);
            } catch (\Exception $e) {
                Log::error("Error syncing genres for title {$title->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("No valid genres found for title: {$title->id}");
        }

        $length = $result['length'];

        $existingEpisodes = Episode::where('title_id', $this->id)
            ->select('episode_index')
            ->pluck('episode_index')
            ->toArray();

        $jobs = [];

        for ($i = 1; $i <= $length; $i++) {
            if (!in_array($i, $existingEpisodes)) {
                $episodeId = "{$this->id}-episode-{$i}";
                $jobs[] = new ProcessEpisode($episodeId);
            }
        }

        $job_len = count($jobs);

        Log::info("Processing $job_len episodes.");

        if (!empty($jobs)) {
            Bus::batch($jobs)
                ->progress(function (Batch $batch) use ($job_len, $id) {
                    Log::info('Processed ' . $batch->processedJobs() . " out of $job_len episodes. (Title ID: $id)");
                })
                ->then(function (Batch $batch) use ($length, $id) {
                    Log::info("Successfully processed $length episodes (ID: $id)");
                })
                ->dispatch();
        }

        Log::info("Title successfully processed (ID: $id)");
    }
}
