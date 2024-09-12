<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    public $timestamps = false;
    protected $table = 'episodes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'episode_index',
        'title_id',
        'upload_date',
        'video',
        'views',
    ];

    protected $casts = [
        'id' => 'string',
        'episode_index' => 'string',
        'title_id' => 'string',
        'upload_date' => 'string',
        'video' => 'array',
        'views' => 'integer'
    ];
}
