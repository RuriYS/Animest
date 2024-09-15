<?php

namespace App\Utils;

class AnimeParser
{
    public static function parseSeason(string $str): string
    {
        $str = explode('/', $str)[2];
        $str = explode('-', $str)[0];
        return $str;
    }
}
