<?php

use LionRoute\Route;

use App\Http\Request\Response;

// || ------------------------------------------------------------------------------
// || Web Routes
// || Here is where you can register web routes for your application.
// || ------------------------------------------------------------------------------
Route::init();

Route::any('/', function() {
	return (Response::getInstance())->response('success', 'Welcome to index!');
});

Route::processOutput(Route::dispatch(3));