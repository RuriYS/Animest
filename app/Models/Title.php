<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Title extends Model {
    public    $incrementing = false;
    protected $primaryKey   = 'id';
    protected $keyType      = 'string';
    protected $table        = 'titles';
    protected $hidden       = ['pivot'];

    protected $fillable = [
        'alias',
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

    protected $casts = [
        'alias'       => 'string',
        'description' => 'string',
        'id'          => 'string',
        'language'    => 'string',
        'length'      => 'integer',
        'names'       => 'string',
        'origin'      => 'string',
        'season'      => 'string',
        'splash'      => 'string',
        'status'      => 'string',
        'title'       => 'string',
        'year'        => 'string',
    ];

    public function episodes(): HasMany {
        return $this->hasMany(Episode::class);
    }

    public function genres(): BelongsToMany {
        return $this->belongsToMany(
            Genre::class,
            'title_genre',
            'title_id',
            'genre_id',
        )
            ->using(TitleGenre::class)
            ->without('pivot');
    }
}
