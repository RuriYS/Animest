<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model {
    public    $incrementing = false;
    protected $primaryKey   = 'id';
    protected $keyType      = 'string';
    protected $table        = 'episodes';
    protected $hidden       = ['pivot'];

    protected $fillable = [
        'id',
        'alias',
        'download_url',
        'episode_index',
        'title_id',
        'upload_date',
        'video',
    ];

    protected $casts = [
        'id'            => 'string',
        'download_url'  => 'string',
        'episode_index' => 'string',
        'title_id'      => 'string',
        'upload_date'   => 'string',
        'video'         => 'array',
    ];

    public function title() {
        return $this->belongsTo(Title::class);
    }
}
