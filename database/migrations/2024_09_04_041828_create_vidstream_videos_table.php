<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vidstream_videos', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->json('meta');
            $table->json('video');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vidstream_videos');
    }
};
