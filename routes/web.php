<?php

use LionRoute\Route;

use App\Http\Middleware\AuthorizeJWT;

use App\Models\Class\Request;
use App\Http\Controllers\{ Controller, HomeController };
use App\Http\Controllers\Auth\{ LoginController, RegisterController, DocumentTypesController };
use App\Http\Controllers\Users\ProfileController;

Controller::content();
Route::init([
    'class' => [
        'RouteCollector' => Phroute\Phroute\RouteCollector::class,
        'Dispatcher' => Phroute\Phroute\Dispatcher::class
    ],
    'middleware' => [
        Route::newMiddleware('exist-jwt', AuthorizeJWT::class, 'existJWT'),
        Route::newMiddleware('authorize-jwt', AuthorizeJWT::class, 'authorizeJWT')
    ]
]);

Route::any('/', [HomeController::class, 'index']);

Route::prefix('api', function() {
    Route::get('read-document-types', [DocumentTypesController::class, 'readDocumentTypes']);

    Route::prefix('auth', function() {
        Route::post('signin', [LoginController::class, 'auth']);
        Route::post('signup', [RegisterController::class, 'createUser']);
    });

    Route::middleware(['before' => 'exist-jwt', 'after' => 'authorize-jwt'], function() {
        Route::post('example-athorize', [ProfileController::class, 'info']);
    });
});

Route::processOutput(Route::dispatch(3));