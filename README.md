# Lion-PHP
Framework for PHP in order to make the code cleaner and simpler.

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-php/v)](https://packagist.org/packages/lion-framework/lion-php) [![Total Downloads](http://poser.pugx.org/lion-framework/lion-php/downloads)](https://packagist.org/packages/lion-framework/lion-php) [![Latest Unstable Version](http://poser.pugx.org/lion-framework/lion-php/v/unstable)](https://packagist.org/packages/lion-framework/lion-php) [![License](http://poser.pugx.org/lion-framework/lion-php/license)](https://packagist.org/packages/lion-framework/lion-php) [![PHP Version Require](http://poser.pugx.org/lion-framework/lion-php/require/php)](https://packagist.org/packages/lion-framework/lion-php)

## Install
```
composer create-project lion-framework/lion-php
```

## Usage
### 1. ROUTES
Lion-Route has been implemented for route handling. More information at [Lion-Route](https://github.com/Sleon4/Lion-Route).
```php
use LionRoute\Route;

use App\Http\Controllers\HomeController;

Route::init();

Route::any('/', [HomeController::class, 'index']);

Route::processOutput(Route::dispatch(3));
```

### 2. RESPONSE
A basic internal response management system has been implemented, the available options are:
1. response(type, message, data)
2. success(message, data)
3. error(message, data)
4. warning(message, data)
5. info(message, data)
6. toResponse(info)

```php
use LionRoute\Route;
use App\Http\Response;

use App\Http\Controllers\HomeController;

Route::init();

Route::any('/', [HomeController::class, 'index']);

Route::any('example', function() {
	return (Response::getInstance())->response('success', 'Welcome to example!');
	// return (Response::getInstance())->success('Welcome to example!');
	// return (Response::getInstance())->error('Welcome to example!');
	// return (Response::getInstance())->warning('Welcome to example!');
	// return (Response::getInstance())->info('Welcome to example!');
});

Route::processOutput(Route::dispatch(3));
```


### 3. REQUEST
A basic internal request management system has been implemented. Currently, it only has the collection of data sent through HTTP requests.
```php
/*
Web.php
*/
use LionRoute\Route;
use App\Http\Response;

use App\Http\Controllers\HomeController;

Route::init();

Route::any('/', [HomeController::class, 'index']);

Route::any('example', function() {
	return (Response::getInstance())->success('Welcome to example!');
});

Route::get('profile/{name}/{last_name}', [HomeController::class, 'example']);

Route::processOutput(Route::dispatch(3));

/*
HomeController.php
*/

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		return $this->response->warning('Page not found. [index]');
	}

	public function example($name, $last_name) {
		return $this->response->success("Welcome {$name} {$last_name}");
	}

}
```


## Credits
[PHRoute](https://github.com/mrjgreen/phroute) <br>
[PHP dotenv](https://github.com/vlucas/phpdotenv) <br>
[Valitron](https://github.com/vlucas/valitron) <br>
[PHPMailer](https://github.com/PHPMailer/PHPMailer) <br>
[PHP-JWT](https://github.com/firebase/php-jwt)

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-PHP/blob/main/LICENSE)