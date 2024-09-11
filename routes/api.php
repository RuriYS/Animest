<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EpisodeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/spider')
    ->group(function () {
        Route::prefix('/vidstream')
            ->group(function () {
                Route::get('{episode_id}/get', [EpisodeController::class, 'get']);
            });
    });

Route::prefix('/videos')
    ->group(function () {
        Route::get('/{anime_id}', [EpisodeController::class, 'index']);
        Route::get('/{anime_id}/{index}', [EpisodeController::class, 'show']);
    });

Route::prefix('/animes')
    ->group(function () {
        Route::get('/', [AnimeController::class, 'index']);
        Route::get('/{id}', [AnimeController::class, 'show']);
    });
