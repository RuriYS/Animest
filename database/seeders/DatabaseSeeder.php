<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $genres = ["Action", "Adult Cast", "Adventure", "Anthropomorphic", "Avant Garde", "Boys Love", "Cars", "CGDCT", "Childcare", "Comedy", "Comic", "Crime", "Crossdressing", "Cultivation", "Delinquents", "Dementia", "Demons", "Detective", "Drama", "Dub", "Ecchi", "Erotica", "Family", "Fantasy", "Gag Humor", "Game", "Gender Bender", "Gore", "Gourmet", "Harem", "Hentai", "High Stakes Game", "Historical", "Horror", "Isekai", "Iyashikei", "Josei", "Kids", "Love Polygon", "Magic", "Magical Sex Shift", "Mahou Shoujo", "Martial Arts", "Mecha", "Medical", "Military", "Music", "Mystery", "Mythology", "Organized Crime", "Parody", "Performing Arts", "Pets", "Police", "Psychological", "Racing", "Reincarnation", "Romance", "Romantic Subtext", "Samurai", "School", "Sci-Fi", "Seinen", "Shoujo", "Shoujo Ai", "Shounen", "Showbiz", "Slice of Life", "Space", "Sports", "Strategy Game", "Strong Male Lead", "Super Power", "Supernatural", "Survival", "Suspense", "system", "Team Sports", "Thriller", "Time Travel", "Vampire", "Video Game", "Visual Arts", "Work Life", "Workplace", "Yaoi", "Yuri"];

        foreach ($genres as $genre) {
            Genre::create([
                'name' => $genre,
            ]);
        }
    }
}
