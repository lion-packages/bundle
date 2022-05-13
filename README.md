# Lion-Framework
Framework for PHP in order to make the code cleaner and simpler.

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-framework/v)](https://packagist.org/packages/lion-framework/lion-framework) [![Total Downloads](http://poser.pugx.org/lion-framework/lion-framework/downloads)](https://packagist.org/packages/lion-framework/lion-framework) [![Latest Unstable Version](http://poser.pugx.org/lion-framework/lion-framework/v/unstable)](https://packagist.org/packages/lion-framework/lion-framework) [![License](http://poser.pugx.org/lion-framework/lion-framework/license)](https://packagist.org/packages/lion-framework/lion-framework) [![PHP Version Require](http://poser.pugx.org/lion-framework/lion-framework/require/php)](https://packagist.org/packages/lion-framework/lion-framework)

## Install
```
composer create-project lion-framework/lion-framework
```

```
composer install
```

# Lion-Framework the API Backend
Lion-Framework can also serve as an API backend for a JavaScript single page application or a mobile application. For example, you can use Lion-Framework as an API backend for your Vite.js app or Kotlin app. <br>

You can use Lion-Framework to provide authentication and data storage/retrieval for your application, while taking advantage of Lion-Framework services such as emails, databases, and more.

## Usage
## Commands
More information about the use of internal commands. [Lion-Command](https://github.com/Sleon4/Lion-Command)

## Security
Learn more about using security features. [Lion-Security](https://github.com/Sleon4/Lion-Security)

## Carbon
The Carbon class inherits from the PHP DateTime class and is installed by default. [nesbot/carbon](https://carbon.nesbot.com/)

### 1. ROUTES AND MIDDLEWARE
Lion-Route has been implemented for route handling. More information at [Lion-Route](https://github.com/Sleon4/Lion-Route). <br>
Middleware is easy to implement. They must have the main class imported into Middleware, which initializes different functions and objects at the Middleware level. <br>
The rule for middleware is simple, in the constructor they must be initialized with the $this->init() function. More information about the use of Middleware in [Lion-Route](https://github.com/Sleon4/Lion-Route). <br>
You can create a middleware with the command.
```
php lion new:middleware HomeMiddleware
```
```php
namespace App\Http\Middleware;

use App\Http\Middleware\Middleware;

class HomeMiddleware extends Middleware {

	public function __construct() {
		$this->init();
	}

	public function example(): void {
		if (!$this->request->user_session) {
			$this->processOutput(
				$this->response->error('Username does not exist.')
			);
		}
	}

}
```

### 2. RESPONSE
A basic internal response management system has been implemented, the available options are:
1. response(type, message, data)
2. success(message, data)
3. error(message, data)
4. warning(message, data)
5. info(message, data)

```php
use LionRoute\Route;
use App\Http\Request\Response;

Route::init();

Route::any('/', function() {
	return Response::response('success', 'Welcome to index!');
	// return Response::success('Welcome to index!');
	// return Response::error('Welcome to index!');
	// return Response::warning('Welcome to index!');
	// return Response::info('Welcome to index!');
});

Route::processOutput(Route::dispatch(3));
```

```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		return $this->response->response('warning', 'Page not found. [index]');
	}

}
```

### 3. CONTROLLERS
Controllers are easy to implement. They must have the parent class imported into `Controller`, which initializes different functions and objects at the Controller level. <br>
The rule for Controllers is simple, in the constructor they must be initialized with the `$this->init()` function. <br>
You can create a controller with the command.
```
php lion new:controller HomeController
```
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		return $this->response->warning('Page not found. [index]');
	}

}
```

#### 3.1 REQUEST
A basic internal request management system has been implemented. Currently you only have data collection sent via HTTP requests and .env variable.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		$myEnv = $this->env; // available .env environment variables
		return $this->response->success("Welcome {$this->request->name} {$this->env->SERVER_URL}");
	}

}
```

#### 3.2 JSON
An internal class has been implemented for handling JSON objects. It has 2 basic functions `encode` and `decode`.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		$data = [
			'name' => "Sergio León",
			'email' => "lion-framework@lion.com"
		];

		$encode = $this->json->encode($data);
		$decode = $this->json->decode($encode);

		return $this->response->success("Welcome", $decode);
	}

}
```

### 4. MODELS
The models are easy to implement. They must have the main class imported into `Model`, which initializes various functions and objects at the model level. <br>
The rule for models is simple, in the constructor they must be initialized with the `$this->init()` function. <br>
You can create a model with the command.
```
php lion new:model HomeModel
```
```php
namespace App\Models;

use App\Models\Model;

class HomeModel extends Model {

	public function __construct() {
		$this->init();
	}

}
```

Note that when you want to implement methods that implement processes with databases, the `LionSql\Drivers\MySQLDriver` class must be imported for their respective operation. more information on [Lion-SQL](https://github.com/Sleon4/Lion-SQL). <br>
Note that at the framework level Lion-SQL is already installed and implemented, the variables are located in the `.env` file, follow the import instructions for their use.

## Credits
[PHRoute](https://github.com/mrjgreen/phroute) <br>
[PHP dotenv](https://github.com/vlucas/phpdotenv) <br>
[Valitron](https://github.com/vlucas/valitron) <br>
[PHPMailer](https://github.com/PHPMailer/PHPMailer) <br>
[PHP-JWT](https://github.com/firebase/php-jwt) <br>
[Symfony-Console](https://github.com/symfony/console) <br>
[Carbon](https://carbon.nesbot.com/)

## Other libraries
[Lion-SQL](https://github.com/Sleon4/Lion-SQL) <br>
[Lion-Security](https://github.com/Sleon4/Lion-Security) <br>
[Lion-Route](https://github.com/Sleon4/Lion-Route) <br>
[Lion-Mailer](https://github.com/Sleon4/Lion-Mailer) <br>
[Lion-Files](https://github.com/Sleon4/Lion-Files) <br>
[Lion-Command](https://github.com/Sleon4/Lion-Command)

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-Framework/blob/main/LICENSE)