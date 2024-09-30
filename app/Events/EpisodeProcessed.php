<?php

namespace App\Events;

use App\Models\Episode;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EpisodeProcessed {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Episode $episode;
    public function __construct(
        Episode $episode,
    ) {
        $this->episode = $episode;
    }
}
