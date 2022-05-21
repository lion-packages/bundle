<?php

use LionRoute\Route;

use LionRequest\Response;

// || ------------------------------------------------------------------------------
// || Web Routes
// || Here is where you can register web routes for your application.
// || ------------------------------------------------------------------------------
Route::init();

Route::get('/', function() {
	return Response::success('Welcome to index! ' . Carbon\Carbon::now());
});

Route::dispatch(3);