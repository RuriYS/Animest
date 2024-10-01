<?php

namespace App\Utils;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class CacheUtils {
    public static function updateMediaCache(string $key, Model|Collection $data): void {
        $ttl = floor(now()->diffInSeconds(now()->addHours(4)));

        $cacheData = [
            'created_at' => now(),
            'result'     => $data instanceof Collection ? $data->toArray() : $data,
            'status'     => true,
            'ttl'        => $ttl,
        ];

        Cache::put($key, $cacheData, $ttl);
    }

    public static function getMediaCache(string $key): ?array {
        $cached = Cache::get($key);

        if ($cached) {
            $age = floor($cached['created_at']->diffInSeconds(now()));
            unset($cached['created_at']);

            return [...$cached, 'age' => $age];
        }

        return null;
    }
}
