<?php

use LionRoute\Route;
use LionSQL\Drivers\MySQL as DB;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', fn() => info("Welcome to index"));

Route::get('example', function() {
    return DB::table('users')->select()->getAll();
});