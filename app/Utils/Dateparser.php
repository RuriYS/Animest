<?php

namespace App\Utils;

use DateTime;
use DateTimeZone;
use Log;

class Dateparser
{
    public function parseDate(string $date): string
    {
        if (preg_match('/\bago\b/', $date)) {
            $timestamp = strtotime($date);
            $utcDate = new DateTime("@$timestamp");
            $utcDate->setTimezone(new DateTimeZone('PST'));
            return $utcDate->format('Y-m-d H:i:s');
        } else {
            try {
                $formattedDate = new DateTime($date);
                return $formattedDate->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return $date;
            }
        }
    }
}
