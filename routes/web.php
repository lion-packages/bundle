<?php

use App\Class\FilesCustomers;
use LionRoute\Route;

use App\Http\Controllers\HomeController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', [HomeController::class, 'index']);

Route::post('send-data', function() {
    return new FilesCustomers();
});