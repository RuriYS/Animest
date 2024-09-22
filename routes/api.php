<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\EpisodeController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [MainController::class, 'test']);

Route::prefix('/spider')
    ->group(function () {
        Route::prefix('/vidstream')
            ->group(function () {
                Route::get('{episode_id}/get', [EpisodeController::class, 'get']);
            });
    });

Route::prefix('/episodes')
    ->group(function () {
        Route::get('/{title_id}', [EpisodeController::class, 'index']);
        Route::get('/{title_id}/{index}', [EpisodeController::class, 'show']);
        Route::post('/{title_id}/{index}', [EpisodeController::class, 'view']);
        Route::get('/{title_id}/{index}/process', [EpisodeController::class, 'process']);
    });

Route::prefix('/titles')
    ->group(function () {
        Route::get('/{title_id}', [TitleController::class, 'show']);
        Route::get('/{title_id}/process/{params?}', [TitleController::class, 'process'])
            ->where('params', '.*');
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
