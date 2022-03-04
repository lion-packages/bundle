<?php

use App\Config\PHPRoute as Route;
use App\Models\Class\Request;
use App\Http\Middleware\UserAuth;
use App\Http\Controllers\Authentication\LoginController;

Route::sessions();
Route::fileGetContents();

Route::router([
	Route::newMiddleware('auth', UserAuth::class, 'auth'),
	Route::newMiddleware('no-auth', UserAuth::class, 'noAuth')
]);

Route::any("/", function() {
	Route::processOutput(
		new Request('warning', "Page not found")
	);
});

Route::middleware(['before' => "no-auth"], function() {
	Route::prefix('/autenticar', function() {
		Route::post('/ingresar', [LoginController::class, 'login']);
	});
});

Route::processOutput(Route::dispatch());