<?php

use LionRoute\Route;

use App\Http\Middleware\JWT\AuthorizationControl;

use App\Http\Controllers\HomeController;

Route::init();

Route::any('/', [HomeController::class, 'index']);

Route::processOutput(Route::dispatch(3));