<?php

namespace App\Jobs;

use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Bus;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
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
        Log::info("Processing $length episodes");

        $jobs = [];
        for ($i = 1; $i <= $length; $i++) {
            $episodeId = "{$this->id}-episode-{$i}";
            $jobs[] = new ProcessEpisode($episodeId);
        }

        Bus::batch($jobs)
            ->progress(function (Batch $batch) {
                Log::info('Progress: ' . $batch->progress() . '/100');
            })
            ->then(function (Batch $batch) use ($length, $id) {
                Log::info("Successfully processed $length episodes (ID: $id)");
            })
            ->dispatch();
    }
}
