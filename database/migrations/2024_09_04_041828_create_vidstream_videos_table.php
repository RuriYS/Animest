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
            $table->string('episode_id');
            $table->string('video_id');
            $table->string('type');
            $table->string('download_uri');
            $table->string('stream_main');
            $table->string('stream_bak');
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
