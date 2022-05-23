<?php

use LionRoute\Route;
use LionRequest\Response;
use Carbon\Carbon;

// || ------------------------------------------------------------------------------
// || Web Routes
// || Here is where you can register web routes for your application.
// || ------------------------------------------------------------------------------

Route::get('/', function() {
    return Response::success('Welcome to index! ' . Carbon::now());
});