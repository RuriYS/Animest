<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('/spider')
    ->group(function () {
        Route::prefix('/vidstream')
            ->group(function () {
                Route::get('{episode_id}/get', [VideoController::class, 'get']);
            });
    });

Route::prefix('/videos')
    ->group(function () {
        Route::get('/{anime_id}', [VideoController::class, 'index']);
        Route::get('/{anime_id}/episode-{index}', [VideoController::class, 'show']);
    });
