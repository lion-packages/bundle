# This library has a quick use of the router with regular expressions based on [mrjgreen's phroute](https://github.com/mrjgreen/phroute).

## Install
```
composer require lion-framework/lion-route
```

## Usage
```php
require_once("vendor/autoload.php");
spl_autoload_register(function($class_name) {
    require_once(str_replace("\\", "/", $class_name) . '.php');
});

use LionRoute\Route;
use LionRoute\Request;

Route::init([
    'class' => [
        'RouteCollector' => Phroute\Phroute\RouteCollector::class,
        'Dispatcher' => Phroute\Phroute\Dispatcher::class
    ]
]);

Route::any('/', function() {
    Route::processOutput(new Request(
        'success', 
        'hello world'
    ));
});

// or

Route::any('/', function() {
    Route::processOutput([
        'status' => "warning",
        'message' => "Hello world."
    ]);
});

// 1 is for production and 2 for local environment.
Route::processOutput(Route::dispatch(2)); 
```

### Defining routes:
```php
use LionRoute\Route;

Route::init([
    'class' => [
        'RouteCollector' => Phroute\Phroute\RouteCollector::class,
        'Dispatcher' => Phroute\Phroute\Dispatcher::class
    ]
]);

Route::get($route, $handler);
Route::post($route, $handler);
Route::put($route, $handler);
Route::delete($route, $handler);
Route::any($route, $handler);
```

This method accepts the HTTP method the route must match, the route pattern and a callable handler, which can be a closure, function name or `['ClassName', 'method']`. [more information in...](https://github.com/mrjgreen/phroute#defining-routes)

### Regex Shortcuts:
```
:i => :/d+                # numbers only
:a => :[a-zA-Z0-9]+       # alphanumeric
:c => :[a-zA-Z0-9+_\-\.]+  # alnumnumeric and + _ - . characters 
:h => :[a-fA-F0-9]+       # hex

use in routes:

'/user/{name:i}'
'/user/{name:a}'
```

### ~~Filters~~ Middleware:
is identical to filters, we change the name of `filter` to `middleware`.
`Route::newMiddleware('auth', Auth::class, 'auth')` is the basic syntax for adding a middleware to our RouteCollector object, The first parameter is the name of the middleware, The second parameter is the class to which that is referenced and the third parameter the name of the function to which it belongs.
```php
use LionRoute\Route;
use App\Http\Middleware\Auth;

Route::init([
    'class' => [
        'RouteCollector' => Phroute\Phroute\RouteCollector::class,
        'Dispatcher' => Phroute\Phroute\Dispatcher::class
    ],
    'middleware' => [
        Route::newMiddleware('auth', Auth::class, 'auth')
    ]
]);

Route::middleware(['before' => 'auth'], function() {
    Route::post('/login', function() {
        Route::processOutput([
            'status' => "success",
            'message' => "Hello world."
        ]);
    });
});
```

### Prefix Groups:
```php
Route::prefix('/authenticate', function() {
    Route::post('/login', function() {
        Route::processOutput([
            'status' => "success",
            'message' => "Hello world."
        ]);
    });
});
```

### Example methods:
#### POST
```php
Route::post('/example-url', function() {
    $loginController = new App\Http\Controllers\Login();
    $loginController->loginAuth();
});

// or

Route::post('/example-url', [App\Http\Controllers\Login::class, 'loginAuth']);
```

#### PUT
```php
Route::put('/example-url/{id}', function($id) {
    $loginController = new App\Http\Controllers\Login();
    $loginController->loginAuth();
});

// or

Route::post('/example-url/{id}', [App\Http\Controllers\Login::class, 'loginAuth']);
```