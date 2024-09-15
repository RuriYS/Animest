<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title')->nullable();
            $table->string('language')->nullable();
            $table->string('length')->nullable();
            $table->text('description')->charset('binary')->nullable();
            $table->string('names')->nullable();
            $table->string('origin')->nullable();
            $table->string('season')->nullable();
            $table->string('splash')->nullable();
            $table->string('status')->nullable();
            $table->year('year')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
