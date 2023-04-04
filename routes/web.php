<?php

use LionRoute\Route;
use App\Traits\Framework\PostmanCollector;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::post('/', function() {
    return info("Welcome to index");
});

Route::get('read-users', function() {
    return [];
});

Route::put('example/dev/create', function() {
    return info("Info");
});

Route::delete('delete', function() {
    return request;
});