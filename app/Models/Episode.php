<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    public $timestamps = false;
    protected $table = 'episodes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'download_url',
        'episode_index',
        'title_id',
        'upload_date',
        'video',
    ];

    protected $casts = [
        'id' => 'string',
        'download_url' => 'string',
        'episode_index' => 'string',
        'title_id' => 'string',
        'upload_date' => 'string',
        'video' => 'array',
    ];

    public function title()
    {
        return $this->belongsTo(Title::class);
    }
}
