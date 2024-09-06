<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vidstream_videos', function (Blueprint $table) {
            $table->string('episode_id')->primary();    // eg; no-game-no-life-episode-1
            $table->string('title');                    // eg; No Game No Life Episode 1
            $table->string('video_id');                 // unique 8 characters identifier
            $table->string('type');                     // SUB/DUB
            // $table->string('download_uri');             // uri
            $table->string('source');                   // m3u8 source file
            $table->string('source_bk');                // m3u8 backup source file
            $table->string('date_added');               // either YYYY-MM-DD HH:MM:SS or relative (N hours/minutes/seconds ago) need format
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vidstream_videos');
    }
};
