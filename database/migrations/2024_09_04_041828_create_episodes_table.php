<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title_id');
            $table->string('download_url')->nullable();
            $table->string('episode_index')->nullable();
            $table->string('upload_date')->nullable();
            $table->json('video')->nullable();

            $table->foreign('title_id')->references('id')->on('titles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
