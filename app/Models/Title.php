<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Title extends Model
{
    protected $table = 'titles';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'description',
        'id',
        'language',
        'length',
        'names',
        'origin',
        'season',
        'splash',
        'status',
        'title',
        'year',
    ];

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'title_genre',
            'title_id',
            'genre_id'
        )->using(TitleGenre::class);
    }
}
