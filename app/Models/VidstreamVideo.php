<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VidstreamVideo extends Model
{
    use HasFactory;

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
