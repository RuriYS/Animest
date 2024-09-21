<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TitleGenre extends Pivot {
    protected $table      = 'title_genre';
    public    $timestamps = false;
}
