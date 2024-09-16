<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    protected $primaryKey = 'id';

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany(
            Title::class,
            'title_genre',
            'genre_id',
            'title_id'
        )->using(TitleGenre::class);
    }
}
