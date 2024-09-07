<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class VidstreamVideo extends Model
{
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
