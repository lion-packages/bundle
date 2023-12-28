<?php

declare(strict_types=1);

// -----------------------------------------------------------------------------
// require_once('../app/Http/Controllers/ExampleController.php');
// require_once('../app/Http/Controllers/UsersController.php');
// -----------------------------------------------------------------------------
// use App\Http\Controllers\ExampleController;
// use LionRoute\Route;
use LionBundle\Routes\Route;
// Inyection -------------------------------------------------------------------
// $container = new Container();
// $container->load()->get(ExampleController::class);
// -----------------------------------------------------------------------------
Route::init();
// -----------------------------------------------------------------------------
Route::addMiddleware([]);
// -----------------------------------------------------------------------------
Route::get('/', fn() => info('[index]'));
Route::get('logger', function() {
    logger('test-logger', 'info', ['user' => 'Sleon'], true);
    return success();
});
// Route::get('controller', [ExampleController::class, 'createExample']);
Route::get('route-list', fn() => Route::getFullRoutes());
// -----------------------------------------------------------------------------
Route::dispatch();
