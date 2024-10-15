<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use App\Models\Episode;
use App\Utils\CacheManager;
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
        // Log::debug('[makeResponse]');
        Log::debug('[makeResponse] Creating response', ['title_id' => $this->title_id]);

        $ttl = floor(now()->diffInSeconds(now()->addHours(4)));

        return CacheManager::setOrGet($this->key, $ttl, function () {
            Log::debug('[makeResponse] No cache found, creating one', ['title_id' => $this->title_id]);

            $result = $this->isEpisode ? $this->getEpisode() : $this->getTitle();
            $status = !empty($result['data']) || $result['status'];


            Log::debug('[makeResponse] New cache', [
                'title_id' => $this->title_id,
                'result'   => json_encode($result),
                'status'   => $status,
            ]);
            return $result['data'];
        });
    }


    protected function getTitle($process_eps = false, $refresh_eps = false) {
        $data   = Title::with('genres')->find($this->title_id);
        $status = false;

        if ($this->dispatch) {
            $title  = (array) ProcessTitle::dispatchSync($this->title_id, $process_eps, $refresh_eps);
            $status = !empty($title);

        } elseif ($this->sync || empty($data)) {
            $data = ProcessTitle::dispatchSync($this->title_id);
        }

        $response = ['data' => $data, 'status' => $status];

        Log::debug('[getTitle]', ['response' => $response]);

        return $response;
    }

    protected function getEpisode() {
        Log::debug('[getEpisode] Getting episode', ['title_id' => $this->title_id]);

        $title  = Title::find($this->title_id);
        $query  = Episode::where('title_id', $this->title_id);
        $status = false;

        if (!$title || $this->dispatch) {
            Log::debug('[getEpisode] No title found, dispatching.', ['title_id' => $this->title_id]);
            $title = $this->getTitle(true, true);
        }

        if (!$this->episodeIndex) {
            Log::debug('[getEpisode] No index pos specified, Returning the entire index', ['title_id' => $this->title_id]);
            $episode    = $query->orderBy('episode_index');
            $pagination = $episode->paginate(50, ['*'], 'p', $this->page);
            $episode    = ($pagination->isNotEmpty()) ? $pagination : null;
        } else {
            $episode = $query->where('episode_index', $this->episodeIndex)->first() ?? null;
        }

        if (!$episode || $this->sync) {
            Log::debug('[getEpisode] Syncing episodes', ['title_id' => $this->title_id]);
            $title   = ProcessTitle::dispatchSync($this->title_id, true, true);
            $episode = null;

            if ($title) {
                $status = true;
                Log::debug('[getEpisode] Finishing up', ['status' => $status, 'title_id' => $this->title_id]);
            }
        }

        return ['data' => $episode, 'status' => $status];
    }

    protected function forget() {
        Cache::forget($this->key);
    }
}
