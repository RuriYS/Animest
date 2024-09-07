<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class VidstreamVideo extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vidstream_videos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'meta',
        'video'
    ];

    protected $casts = [
        'id' => 'string',
        'meta' => 'array',
        'video' => 'array'
    ];
}
