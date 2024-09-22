<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Title;
use App\Spiders\GogoSpider;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class ProcessTitle implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected string $title_id;

    protected bool $process_eps;

    protected bool $refresh_eps;

    public function __construct(string $title_id, bool $process_eps = true, bool $refresh_eps = true) {
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
            '[ProcessTitle] Processing Title',
            [
                'title_id'    => $title_id,
                'process_eps' => $this->process_eps,
                'refresh_eps' => $this->refresh_eps,
            ],
        );

        try {
            $result = $this->collectSpider($title_id);

            if (isset($result['error'])) {
                Log::warning('Title Job discarded', ['id' => $this->title_id, 'reason' => $result['error']]);
                return;
            }

            $title = $this->updateTitle($result);
            $this->processGenres($result['genres'] ?? [], $title);

            if ($this->process_eps) {
                $this->processEpisodes($result['alias'], $result['length']);
            }

        } catch (\Exception $e) {
            Log::error('Title processing failed', ['title_id' => $title_id, 'error' => $e->getMessage()]);
        }
    }

    private function collectSpider(string $title_id): array {
        $items = Roach::collectSpider(
            GogoSpider::class,
            context: [
                'uri' => 'https://' . config('app.urls.gogo') . "/category/$title_id"
            ],
        );

        $result = array_merge(...array_map(fn($item) => $item->all(), $items));
        Log::debug('[ProcessTitle] Spider collected', ['title_id' => $title_id, 'resultCount' => count($result)]);
        return $result;
    }

    private function updateTitle(array $result): Title {
        $titleData = array_diff_key($result, array_flip(['genres']));
        $title     = Title::updateOrCreate(['id' => $result['id']], $titleData);

        Log::debug('[ProcessTitle] Title updated', ['title_id' => $title->id]);
        return $title;
    }

    private function processGenres(array $genres, Title $title): void {
        $genres = array_map('trim', $genres);
        Log::debug("[ProcessTitle] Processing genres", ['titleId' => $title->id, 'genreCount' => count($genres)]);

        $genreIds = Genre::whereIn('name', $genres)->pluck('id')->toArray();

        if (!empty($genreIds)) {
            try {
                $title->genres()->sync($genreIds);
                Log::debug("[ProcessTitle] Genres synced", ['title_id' => $title->id, 'genreCount' => count($genreIds)]);
            } catch (\Exception $e) {
                Log::error("Error syncing genres", ['title_id' => $title->id, 'error' => $e->getMessage()]);
            }
        } else {
            Log::warning("No valid genres found", ['title_id' => $title->id]);
        }
    }

    private function processEpisodes(string $alias, int $length): void {
        $cached_episodes = Episode::where('alias', $alias)
            ->pluck('episode_index')
            ->toArray();

        $title_id        = $this->title_id;
        $dispatched_jobs = [];

        for ($i = 1; $i <= $length; $i++) {
            if ($this->refresh_eps === true || ($this->refresh_eps === false && !in_array($i, $cached_episodes))) {
                $episode_id        = "{$alias}-episode-{$i}";
                $dispatched_jobs[] = ProcessEpisode::dispatch($episode_id, $title_id)->onQueue('low');
                Log::debug(
                    '[ProcessTitle] Dispatched episode',
                    [
                        'alias'      => $alias,
                        'episode_id' => $episode_id,
                        'title_id'   => $title_id,
                    ],
                );
            }
        }

        if (count($dispatched_jobs) > 0) {
            Log::debug(
                "[ProcessTitle] Episode jobs dispatched",
                [
                    'alias'           => $alias,
                    'cached_episodes' => $cached_episodes,
                    'dispatched_jobs' => count($dispatched_jobs),
                    'episodes'        => $length,
                ],
            );
        } else {
            Log::debug('[ProcessTitle] All episodes are cached, nothing to do.');
        }
    }
}
