<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VidstreamVideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('/home')
->group(function () {
    Route::get('/', [HomeController::class, 'home'])->name('home.index');
});

Route::prefix('/spider')
->group(function () {
    Route::prefix('/vidstream')
    ->group(function () {
        Route::get('{episode_id}', [VidstreamVideoController::class, 'get']);
        Route::get('{episode_id}/store', [VidstreamVideoController::class, 'store']);
    });
});
