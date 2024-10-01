<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;
use App\Models\Episode;
use App\Utils\CacheUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MediaController extends ControllerAbstract {
    private string $title_id;
    private string $key;
    private bool   $dispatch;
    private bool   $refresh;
    private bool   $sync;
    private bool   $isEpisode;
    private ?int   $episodeIndex;

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

        $result = $this->isEpisode ? $this->processEpisode() : $this->processTitle();

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

    protected function processTitle($process_eps = false, $refresh_eps = false) {
        $result = Title::find($this->title_id);
        $status = false;

        if ($this->dispatch) {
            $title  = (array) ProcessTitle::dispatchSync($this->title_id, $process_eps, $refresh_eps);
            $status = !empty($title);

        } elseif ($this->sync || empty($result)) {
            $result = Title::with('genres')->find($this->title_id)?->toArray();
            $result = ProcessTitle::dispatchSync($this->title_id);
        }

        return ['data' => $result, 'status' => $status];
    }

    protected function processEpisode() {
        $title = Title::find($this->title_id);
        $query = Episode::where('title_id', $this->title_id);

        if (!$title || $this->dispatch) {
            $this->processTitle(true, true);
        }

        $data = ($this->episodeIndex !== null) ?
            $query->where('episode_index', $this->episodeIndex)->first() :
            $query->get()->toArray();

        $status = false;

        if (empty($data) || $this->sync) {
            $title = ProcessTitle::dispatchSync($this->title_id, true, true);
            $data  = null;

            if ($title)
                $status = true;
        }

        return ['data' => $data, 'status' => $status];
    }

    protected function forget() {
        Cache::forget($this->key);
    }
}
