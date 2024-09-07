<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VidstreamVideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('/spider')
    ->group(function () {
        Route::prefix('/vidstream')
            ->group(function () {
                Route::get('{episode_id}/get', [VidstreamVideoController::class, 'get']);
            });
    });

Route::prefix('/videos')
    ->group(function () {
        Route::get('/{anime_id}', [VidstreamVideoController::class, 'index']);
        Route::get('/{anime_id}/episode-{index}', [VidstreamVideoController::class, 'show']);
    });
