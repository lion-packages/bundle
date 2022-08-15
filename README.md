# Lion-Framework

A simple and easy to use PHP framework

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-framework/v)](https://packagist.org/packages/lion-framework/lion-framework) [![Total Downloads](http://poser.pugx.org/lion-framework/lion-framework/downloads)](https://packagist.org/packages/lion-framework/lion-framework) [![License](http://poser.pugx.org/lion-framework/lion-framework/license)](https://packagist.org/packages/lion-framework/lion-framework) [![PHP Version Require](http://poser.pugx.org/lion-framework/lion-framework/require/php)](https://packagist.org/packages/lion-framework/lion-framework)

#### Note: very soon youtube tutorials on the basic operation of the framework

## Install

```shell
composer create-project lion-framework/lion-framework
```

# Lion-Framework the API Backend

Lion-Framework can also serve as an API backend for a JavaScript single page application or a mobile application. For example, you can use Lion-Framework as an API backend for your Vite.js app or Kotlin app <br>

You can use Lion-Framework to provide authentication and data storage/retrieval for your application, while taking advantage of Lion-Framework services such as emails, databases, and more

## Usage

Start by running the server, by default it runs on port `8000`

```shell
php lion serve
```

use another port

```shell
php lion serve --port=8001
```

## Commands

More information about the use of internal commands [Lion-Command](https://github.com/Sleon4/Lion-Command)

```shell
php lion serve
php lion new:controller name_controller
php lion new:model name_model
php lion new:middleware name_middleware
php lion new:command name_command
php lion new:capsule name_capsule
php lion new:test name_test
php lion new:rule name_rule
php lion key:rsa
php lion test
php lion token:jwt
php lion route:list
```

## Optional Parameters

```shell
php lion serve --port=8001
php lion key:rsa --path="storage/other-secret-key/"
```

## Route list

To view the available routes, start the local server first, run the `php lion serve` command, and then view the routes

```shell
php lion route:list
```

Warning note: the routes are loaded with the server route `SERVER_URL` set in .env, modify this route to avoid errors in the execution of the process, in the file `public/index.php` there is a public route which allows get the available routes from the terminal, comment this line once your web app is deployed

## Add commands

The commands must be added in an array from `routes/console.php`

```php
return [
    App\Console\RSACommand::class,
    App\Console\GenerateJWTCommand::class,
    App\Console\RouteListCommand::class
];
```

## Add headers

Headers must be added in an array from `routes/header.php`

```php
LionRequest\Request::header(
    'Content-Type',
    'application/json; charset=UTF-8'
);

LionRequest\Request::header(
    'Access-Control-Allow-Origin',
    env->SERVER_ACCESS_CONTROL_ALLOW_ORIGIN
);

LionRequest\Request::header(
    'Access-Control-Max-Age',
    env->SERVER_ACCESS_CONTROL_MAX_AGE
);

LionRequest\Request::header(
    'Access-Control-Allow-Methods',
    'GET, POST, PUT, DELETE'
);

LionRequest\Request::header(
    'Access-Control-Allow-Headers',
    'Origin, X-Requested-With, Content-Type, Accept, Authorization'
);
```

## REQUEST AND RESPONSE

Learn more about using request features [Lion-Request](https://github.com/Sleon4/Lion-Request)

## SECURITY

Learn more about using security features [Lion-Security](https://github.com/Sleon4/Lion-Security)

## FILES

Learn more about using functions in files [Lion-Files](https://github.com/Sleon4/Lion-Files)

## CARBON

The Carbon class inherits from the PHP DateTime class and is installed by default [nesbot/carbon](https://carbon.nesbot.com/)

### 1. ROUTES AND MIDDLEWARE

Lion-Route has been implemented for route handling. More information at [Lion-Route](https://github.com/Sleon4/Lion-Route), from the web you can add all the necessary routes for the operation of your web application `routes/web.php`

```php
Route::get('/', [HomeController::class, 'index']);
```

You can create middleware from command line `php lion new:middleware middleware_name`. More information about the use of Middleware in [Lion-Route](https://github.com/Sleon4/Lion-Route)

```php
namespace App\Http\Middleware\JWT;

use LionSecurity\JWT;

class AuthorizationMiddleware {

    public function __construct() {

    }

    public function exist(): void {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            response->finish(
                json->encode(
                    response->error('The JWT does not exist')
                )
            );
        }
    }

    public function authorize(): void {
        $headers = apache_request_headers();

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);

            if ($jwt->status === 'error') {
                response->finish(
                    json->encode(
                        response->error($jwt)
                    )
                );
            }
        } else {
            response->finish(
                json->encode(
                    response->error('Invalid JWT')
                )
            );
        }
    }

    public function notAuthorize(): void {
        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            response->finish(
                json->encode(
                    response->error('User in session, You must close the session')
                )
            );
        }
    }

}
```

to add a middleware you must open the middleware file located in `routes/middleware.php`, where there are default middleware for the use of JWT

```php
LionRoute\Route::newMiddleware([
	App\Http\Middleware\JWT\AuthorizationMiddleware::class => [
		['name' => "jwt-exist", 'method' => "exist"],
		['name' => "jwt-authorize", 'method' => "authorize"],
		['name' => "jwt-not-authorize", 'method' => "notAuthorize"]
	]
]);
```

### 2. CONTROLLERS

You can create controllers from the command line `php lion new:controller controller_name`

```php
namespace App\Http\Controllers;

class HomeController {

	public function __construct() {

	}

	public function index() {
		return response->warning('Page not found. [index]');
	}

}
```

### 3. MODELS

You can create models from the command line `php lion new:model model_name`

```php
namespace App\Models;

use LionSql\Drivers\MySQLDriver as Builder;

class HomeModel {

	public function __construct() {

	}

}
```

Note that when you want to implement methods that implement processes with databases, the `LionSql\Drivers\MySQLDriver` class must be imported for their respective operation. more information on [Lion-SQL](https://github.com/Sleon4/Lion-SQL) <br>
Note that at the framework level Lion-SQL is already installed and implemented, the variables are located in the `.env` file, follow the import instructions for their use

### 4. RULES

You can create rules from command line `php lion new:rule rule_name`, rule usage is based on rules provided by [vlucas/valitron](https://github.com/vlucas/valitron), you can configure the language response from environment variables with language preference `.env` [lang](https://github.com/vlucas/valitron/tree/master/lang)

```php
namespace App\Rules;

use LionSecurity\SECURITY;
use App\Traits\DisplayErrors;

class EmailRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): EmailRule {
		$this->validation = SECURITY::validate(
			(array) request, [
				'required' => [
					['users_email']
				],
				'email' => [
					['users_email']
				]
			]
		)->data;

		return $this;
	}

}
```

Add your rules to different routes in `routes/rules.php`

```php
return [
    '/api/auth/signin' => [
        App\Rules\EmailRule::class
    ]
];
```

### DEPLOY HEROKU

Create the Procfile file in the main directory of your project (without extension and with a capital P), and inside it place the following

```
web: vendor/bin/heroku-php-apache2 public/
```

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
[Lion-Command](https://github.com/Sleon4/Lion-Command) <br>
[Lion-Request](https://github.com/Sleon4/Lion-Request)

## License

Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-Framework/blob/main/LICENSE)
