<?php

namespace App\Utils;

class CateParser
{
    public static function parseSeason(string $str): string
    {
        $split = explode('/', $str)[2];
        return explode('-', $split)[0];
    }

    public static function parseEpisodeAlias(string $str): string
    {
        $split = explode('/', $str);
        return $split[-1];
    }

    public static function parseTitleId(string $str): string
    {
        preg_match('/^\/category\/(.+)$/', $str, $matches);
        return $matches[1];
    }

    public static function parseGenre(string $str): string
    {
        preg_match('/^\/genre\/(.+)$/', $str, $matches);
        return $matches[1];
    }

    public static function parseThumbnail(string $str): string
    {
        preg_match('/(https:\/\/[^\'")]+)/', $str, $matches);
        return $matches[1];
    }
}
