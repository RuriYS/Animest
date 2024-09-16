<?php

use App\Http\Controllers\TitleController;
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
        Route::post('/{anime_id}/{index}', [EpisodeController::class, 'view']);
        Route::get('/{anime_id}/{index}/process', [EpisodeController::class, 'process']);
    });

Route::prefix('/titles')
    ->group(function () {
        Route::get('/', [TitleController::class, 'search']);
        Route::get('/{id}', [TitleController::class, 'show']);
        Route::get('/{id}/process', [TitleController::class, 'process']);
    });
