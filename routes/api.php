<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [MainController::class, 'test']);

Route::prefix('/titles')
    ->group(function () {
        Route::get('/{title_id}', [MediaController::class, 'show']);
        Route::get('/{title_id}/episodes/{index?}', [MediaController::class, 'show']);
    });

Route::prefix('/search')
    ->group(function () {
        Route::get('/{params?}', [SearchController::class, 'search'])
            ->where('params', '.*');
        Route::get('/quick/{params?}', [SearchController::class, 'quicksearch'])
            ->where('params', '.*');
    });

Route::prefix('/ajax')
    ->group(function () {
        Route::get('/popular', [AjaxController::class, 'popularReleases']);
    });
