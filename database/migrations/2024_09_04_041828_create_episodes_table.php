<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('episode_index')->nullable();
            $table->string('upload_date')->nullable();
            $table->json('video')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
