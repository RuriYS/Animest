<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('episodes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('alias');
            $table->string('title_id');
            $table->text('download_url')->charset('binary')->nullable();
            $table->integer('episode_index');
            $table->string('upload_date');
            $table->json('video')->nullable();
            $table->timestamps();

            $table->foreign('title_id')->references('id')->on('titles');
        });
    }

    public function down(): void {
        Schema::dropIfExists('episodes');
    }
};
