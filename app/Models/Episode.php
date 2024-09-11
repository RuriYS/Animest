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
        'upload_date',
        'video',
    ];

    protected $casts = [
        'id' => 'string',
        'episode_index' => 'string',
        'upload_date' => 'string',
        'video' => 'array'
    ];
}
