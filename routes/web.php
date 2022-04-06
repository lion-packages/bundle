<?php

use LionRoute\Route;
use App\Http\Response;

use App\Http\Controllers\HomeController;

Route::init();

Route::any('/', [HomeController::class, 'index']);

Route::any('example', function() {
	return (Response::getInstance())->success('Welcome to example!');
});

Route::get('profile/{name}/{last_name}', [HomeController::class, 'example']);

Route::processOutput(Route::dispatch(3));