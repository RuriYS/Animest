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
}
