<?php

namespace App\Jobs;

use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
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
        Log::debug("Processing Title with ID: $this->id");

        $items = Roach::collectSpider(
            GogoSpider::class,
            context: ['id' => $this->id]
        );

        $result = array_merge(
            ...array_map(fn($item) => $item->all(), $items)
        );

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
                \Log::warning("Genre not found: {$genreName}");
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
    }
}
