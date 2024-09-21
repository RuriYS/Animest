<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::get('/{path}', [MainController::class, 'index'])->where('path', '^(?!api).*$');


