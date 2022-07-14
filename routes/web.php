<?php

use LionRoute\Route;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', fn() => LionRequest\Response::success("Welcome to index"));
Route::get('users', fn() => LionRequest\Response::success("All users"));