<?php

namespace App\Utils;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheManager extends Cache {
    public static function updateMediaCache(string $key, mixed $data): void {
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

    public static function setOrGet(string $key, int $ttl, \Closure $callback) {
        $cached = self::get($key);

        if ($cached) {
            $age = floor($cached['created_at']->diffInSeconds(now()));
            unset($cached['created_at']);
            return [...$cached, 'age' => $age];
        }

        $result = $callback();

        if ($result === null) {
            return [
                'result' => null,
                'status' => false,
                'ttl'    => null,
                'age'    => null,
            ];
        }

        $cacheData = [
            'created_at' => now(),
            'result'     => $result instanceof Collection ? $result->toArray() : $result,
            'status'     => true,
            'ttl'        => $ttl,
        ];

        self::put($key, $cacheData, $ttl);

        return [
            'result' => $result,
            'status' => true,
            'ttl'    => $ttl,
            'age'    => 0,
        ];
    }
}
