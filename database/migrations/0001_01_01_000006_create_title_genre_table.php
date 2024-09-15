<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('title_genre', function (Blueprint $table) {
            $table->string('title_id');
            $table->uuid('genre_id');

            $table->foreign('title_id')
                ->references('id')
                ->on('titles')
                ->onDelete('cascade');

            $table->foreign('genre_id')
                ->references('id')
                ->on('genres')
                ->onDelete('cascade');

            $table->primary(['title_id', 'genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('title_genre');
    }
};
