<?php

use LionRoute\Route;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', function() {
    return info("Welcome to index");
});

Route::post('users/create', function() {
    return info("Welcome to create");
});

Route::put('users/update', function() {
    return info("Welcome to update");
});