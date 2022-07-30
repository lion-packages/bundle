<?php

use LionRoute\Route;
use App\Http\Controllers\Auth\LoginController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', fn() => response->success("Welcome to index"));
Route::get('users', fn() => response->success("All users"));