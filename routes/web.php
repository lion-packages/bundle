<?php

use LionRoute\Route;

use App\Http\Middleware\JWT\AuthorizationControl;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\{ LoginController, RegisterController, DocumentTypesController };
use App\Http\Controllers\Users\ProfileController;

Route::init([
    'middleware' => [
        Route::newMiddleware('exist-jwt', AuthorizationControl::class, 'exist'),
        Route::newMiddleware('authorize-jwt', AuthorizationControl::class, 'authorize')
    ]
]);

Route::any('/', [HomeController::class, 'index']);

Route::prefix('api', function() {
    Route::get('read-document-types', [DocumentTypesController::class, 'readDocumentTypes']);

    Route::prefix('auth', function() {
        Route::post('signin', [LoginController::class, 'auth']);
        Route::post('signup', [RegisterController::class, 'createUser']);
    });

    Route::middleware(['exist-jwt', 'authorize-jwt'], function() {
        Route::post('example-athorize', [ProfileController::class, 'info']);
    });
});

Route::processOutput(
    Route::dispatch(3)
);