<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    public $timestamps = false;
    protected $table = 'titles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'title',
        'description',
        'splash',
        'episodes',
    ];

    protected $casts = [
        'id' => 'string',
        'title' => 'string',
        'description' => 'string',
        'splash' => 'string',
        'episodes' => 'array',
    ];
}
