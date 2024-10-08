<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use App\Models\Episode;
use App\Utils\CacheUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MediaController extends ControllerAbstract {
    private string $title_id;
    private string $key;
    private bool   $dispatch;
    private bool   $refresh;
    private bool   $sync;
    private bool   $isEpisode;
    private ?int   $episodeIndex;
    private ?int   $page;

    public function show(Request $request, string $titleId, ?int $episodeIndex = null) {
        $this->title_id     = $titleId;
        $this->isEpisode    = $request->segment(4) === 'episodes';
        $this->episodeIndex = $episodeIndex;
        $this->key          = $this->isEpisode
            ? ($episodeIndex !== null ? "episode:$titleId:$episodeIndex" : "episodes:$titleId")
            : "title:$titleId";

        $this->refresh  = (bool) $request->boolean('r');
        $this->dispatch = (bool) $request->boolean('d');
        $this->sync     = (bool) $request->boolean('s');
        $this->page     = (int) $request->integer('p') ?? 1;

        if ($this->refresh || $this->dispatch) {
            $this->forget();
        }

        $response = $this->makeResponse();
        $status   = $response['status'];
        $retcode  = ($status && empty($response['result'])) ? 202 : ($status ? 200 : 404);

        return response()->json([
            'dispatch' => $this->dispatch,
            'refresh'  => $this->refresh,
            'sync'     => $this->sync,
            'episode'  => $this->isEpisode,
            'status'   => $status,
            'message'  => $response,
        ], $retcode);
    }

    protected function makeResponse() {
        $cached = CacheUtils::getMediaCache($this->key);

        if ($cached) {
            return $cached;
        }

        $result = $this->isEpisode ? $this->getEpisode() : $this->getTitle();
        $status = !empty($result['data']) || $result['status'];

        $cache_rule = in_array(true, [
            $this->refresh,
            $this->sync,
            empty($result['data']) && $status,
        ]);

        if (!$cache_rule) {
            CacheUtils::updateMediaCache($this->key, $result['data']);
        }

        return [
            'result' => $result['data'],
            'status' => $status,
            'ttl'    => $cache_rule ? null : floor(now()->diffInSeconds(now()->addHours(4))),
            'age'    => $cache_rule ? null : 0,
        ];
    }

    protected function getTitle($process_eps = false, $refresh_eps = false) {
        $result = Title::find($this->title_id);
        $status = false;

        if ($this->dispatch) {
            $title  = (array) ProcessTitle::dispatchSync($this->title_id, $process_eps, $refresh_eps);
            $status = !empty($title);

        } elseif ($this->sync || empty($result)) {
            $result = ProcessTitle::dispatchSync($this->title_id);
            // $result = Title::with('genres')->find($this->title_id)?->toArray();
        }

        return ['data' => empty($result) ? null : $result, 'status' => $status];
    }

    protected function getEpisode() {
        $title  = Title::find($this->title_id);
        $query  = Episode::where('title_id', $this->title_id);
        $status = false;

        if (!$title || $this->dispatch) {
            $this->getTitle(true, true);
        }

        if (!$this->episodeIndex) {
            $episode    = $query->orderBy('episode_index');
            $pagination = $episode->paginate(10, ['*'], 'p', $this->page);
            if ($pagination->isNotEmpty()) {
                $episode = $pagination;
            } else {
                $episode = null;
            }
        } else {
            $episode = $query->where('episode_index', $this->episodeIndex)->first() ?? null;
        }

        if ($this->sync) {
            Log::debug('[MediaController] Processing title', ['id' => $this->key]);
            $title   = ProcessTitle::dispatchSync($this->title_id, true, true);
            $episode = null;

            if ($title)
                $status = true;
        }

        return ['data' => $episode, 'status' => $status];
    }

    protected function forget() {
        Cache::forget($this->key);
    }
}
