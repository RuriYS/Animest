<?php

namespace App\Utils;
use Illuminate\Support\Facades\Log;

class CateParser {
    public static function parseSeason(string $str): string {
        $split = explode('/', $str)[2];
        return explode('-', $split)[0];
    }

    public static function parseEpisodeID(string $str): array {
        preg_match('/([^\/]*?)-episode-(\d+)$/', trim($str), $matches);

        return [
            'alias' => $matches[1],
            'index' => $matches[2],
        ];
    }

    public static function parseTitleId(string $str): string {
        preg_match('/^\/category\/(.+)$/', $str, $matches);
        return $matches[1];
    }

    public static function parseGenre(string $str): string {
        preg_match('/^\/genre\/(.+)$/', $str, $matches);
        return $matches[1];
    }

    public static function parseThumbnail(string $str): string {
        preg_match('/(https:\/\/[^\'")]+)/', $str, $matches);
        return $matches[1];
    }
}
