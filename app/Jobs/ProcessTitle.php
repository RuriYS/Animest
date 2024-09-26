<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $title_id;

    protected bool $process_eps;

    protected bool $refresh_eps;

    public function __construct(string $title_id, bool $process_eps = false, bool $refresh_eps = false) {
        $this->title_id    = $title_id;
        $this->process_eps = $process_eps;
        $this->refresh_eps = $refresh_eps;
    }

    public function uniqueId(): string {
        return $this->title_id;
    }

    public function uniqueFor(): int {
        return 3600;
    }

    public function handle(): void {
        $title_id = str($this->title_id)->toString();

        Log::debug(
            '[ProcessTitle] Job started',
            [
                'title_id'    => $title_id,
                'process_eps' => $this->process_eps,
                'refresh_eps' => $this->refresh_eps,
            ],
        );

        try {
            $result = $this->collectSpider($title_id);

            if (isset($result['error'])) {
                Log::warning('[ProcessTitle] Job rejected', [
                    'id'     => $this->title_id,
                    'reason' => $result['error'],
                ]);
                return;
            }

            DB::transaction(function () use ($result) {
                $title = $this->updateTitle($result);
                $this->processGenres($result['genres'] ?? [], $title);

                if ($this->process_eps) {
                    $this->processEpisodes($result['alias'], $result['length']);
                }
            });

        } catch (\Exception $e) {
            Log::error(
                '[ProcessTitle] Job failed',
                ['title_id' => $title_id, 'error' => $e->getMessage()],
            );
        }
    }

    private function collectSpider(string $title_id): array {
        $items = Roach::collectSpider(
            GogoSpider::class,
            context: [
                'base_url' => 'https://' . config('app.urls.gogo') . "/category/$title_id"
            ],
        );

        $result = array_merge(...array_map(fn($item) => $item->all(), $items));
        Log::debug(
            '[ProcessTitle] Spider collected',
            ['title_id' => $title_id, 'resultCount' => count($result)],
        );
        return $result;
    }

    private function updateTitle(array $result): Title {
        $titleData = array_diff_key($result, array_flip(['genres']));
        $title     = Title::updateOrCreate(['id' => $result['id']], $titleData);

        Log::debug('[ProcessTitle] Title updated', ['title_id' => $title->id]);
        return $title;
    }

    private function processGenres(array $genres, Title $title): void {
        $genres   = array_map('trim', $genres);
        $genreIds = Genre::whereIn('name', $genres)->pluck('id')->toArray();

        if (!empty($genreIds)) {
            try {
                $title->genres()->sync($genreIds);
            } catch (\Exception $e) {
                Log::error(
                    "[ProcessTitle] Genre sync failed",
                    ['title_id' => $title->id, 'error' => $e->getMessage()],
                );
            }
        } else {
            Log::warning("[ProcessTitle] No valid genres found", ['title_id' => $title->id]);
        }
    }

    private function processEpisodes(string $alias, int $length): void {
        $currentTime     = now();
        $updateThreshold = $currentTime->subHours(12);

        $existingEpisodes = Episode::where('alias', $alias)
            ->where(function ($query) use ($updateThreshold) {
                $query->whereNull('updated_at')
                    ->orWhere('updated_at', '>', $updateThreshold);
            })
            ->pluck('episode_index')
            ->toArray();

        $episodesToProcess = array_diff(range(1, $length), $existingEpisodes);

        if ($this->refresh_eps) {
            $episodesToProcess = range(1, $length);
        }

        foreach ($episodesToProcess as $i) {
            $episode_id = "{$alias}-episode-{$i}";
            ProcessEpisode::dispatch($episode_id, $this->title_id)->onQueue('low');

            Log::debug('[ProcessTitle] Dispatched episode', [
                'alias'      => $alias,
                'episode_id' => $episode_id,
                'title_id'   => $this->title_id,
            ]);
        }

        Log::debug("[ProcessTitle] Episode jobs dispatched", [
            'alias'           => $alias,
            'dispatched_jobs' => count($episodesToProcess),
            'total_episodes'  => $length,
        ]);
    }
}
