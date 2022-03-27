# Lion-Framework-Backend
Framework for PHP in order to make the code cleaner and simpler.

[![Latest Stable Version](http://poser.pugx.org/lion-framework/lion-backend/v)](https://packagist.org/packages/lion-framework/lion-backend)[![Total Downloads](http://poser.pugx.org/lion-framework/lion-backend/downloads)](https://packagist.org/packages/lion-framework/lion-backend)[![License](http://poser.pugx.org/lion-framework/lion-backend/license)](https://packagist.org/packages/lion-framework/lion-backend)[![PHP Version Require](http://poser.pugx.org/lion-framework/lion-backend/require/php)](https://packagist.org/packages/lion-framework/lion-backend)

## Install
```powershell
composer create-project lion-framework/lion-backend
```

```powershell
composer update
```

## Libraries used
#### Installed by default
##### [Lion-SQL](https://github.com/Sleon4/Lion-SQL)
```powershell
composer require lion-framework/lion-sql
```

##### [Lion-Route](https://github.com/Sleon4/Lion-Route)
```powershell
composer require lion-framework/lion-route
```

##### [Lion-Mailer](https://github.com/Sleon4/Lion-Mailer)
```powershell
composer require lion-framework/lion-mailer
```

##### [Lion-Security](https://github.com/Sleon4/Lion-Security)
```powershell
composer require lion-framework/lion-security
```

___

##### [PHP dotenv](https://github.com/vlucas/phpdotenv)
```powershell
composer require vlucas/phpdotenv
```

#### Installed by other libraries
##### [PHRoute](https://github.com/mrjgreen/phroute)
```powershell
composer require phroute/phroute
```

##### [Valitron](https://github.com/vlucas/valitron)
```powershell
composer require vlucas/valitron
```

##### [PHPMailer](https://github.com/PHPMailer/PHPMailer)
```powershell
composer require phpmailer/phpmailer
```

##### [PHP-JWT](https://github.com/firebase/php-jwt)
```powershell
composer require firebase/php-jwt
```

## Usage
The first step to make use of the framework is to create the database, which will perform user registration and authentication.
It must be taken into account that the given script is a basic part for its initial operation, it is not recommended to use this same script for the development and production environment, the script must be modified according to its required parameters.
```sql
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_user` (IN `_users_email` VARCHAR(100), IN `_users_password` BLOB, IN `_users_name` VARCHAR(25), IN `_users_last_name` VARCHAR(25), IN `_users_document` BIGINT(11), IN `_iddocument_types` INT(11), IN `_users_phone` BIGINT(11))  BEGIN
INSERT INTO users (
	users_email,
	users_password,
	users_name,
	users_last_name,
	users_document,
	iddocument_types,
	users_phone
	)
VALUES (
	_users_email,
	_users_password,
	_users_name,
	_users_last_name,
	_users_document,
	_iddocument_types,
	_users_phone
	);
END$$

DELIMITER ;

CREATE TABLE `document_types` (
	`iddocument_types` int(11) NOT NULL,
	`document_types_name` varchar(45) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE `users` (
	`idusers` int(11) NOT NULL,
	`users_email` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
	`users_password` blob NOT NULL,
	`users_name` varchar(25) COLLATE utf8_spanish_ci NOT NULL,
	`users_last_name` varchar(25) COLLATE utf8_spanish_ci NOT NULL,
	`users_document` bigint(11) NOT NULL,
	`iddocument_types` int(11) NOT NULL,
	`users_phone` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE `validate_login` (
	`users_email` varchar(100)
	,`users_password` blob
);

DROP TABLE IF EXISTS `validate_login`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `validate_login`  AS SELECT `users`.`users_email` AS `users_email`, `users`.`users_password` AS `users_password` FROM `users` ;

ALTER TABLE `document_types`
ADD PRIMARY KEY (`iddocument_types`);

ALTER TABLE `users`
ADD PRIMARY KEY (`idusers`),
ADD UNIQUE KEY `users_phone_UNIQUE` (`users_phone`),
ADD UNIQUE KEY `users_document_UNIQUE` (`users_document`),
ADD UNIQUE KEY `users_email_UNIQUE` (`users_email`),
ADD KEY `users_iddocument_types_FK_idx` (`iddocument_types`);

ALTER TABLE `document_types`
MODIFY `iddocument_types` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
MODIFY `idusers` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
ADD CONSTRAINT `users_iddocument_types_FK` FOREIGN KEY (`iddocument_types`) REFERENCES `document_types` (`iddocument_types`);
COMMIT;
```

The second step to follow is to create the `.env` file. This must have the properties written in the `.env.example`.
Replace all possible data in the `.env` file for the correct functioning of the framework.

The public and private keys must be created for the correct operation of the integrated authentication in the system. <br>
Execute an HTTP request to a route where it generates the respective keys.

```php
use LionSecurity\RSA;
use LionFunctions\FILES;

Route::post('/', function() {
	FILES::folder('path');
	RSA::createKeys('path');

	return [
		'status' => 'success',
		'message' => 'Keys created successfully.'
	];
});
```

## Credits
[PHRoute](https://github.com/mrjgreen/phroute) <br>
[PHP dotenv](https://github.com/vlucas/phpdotenv) <br>
[Valitron](https://github.com/vlucas/valitron) <br>
[PHPMailer](https://github.com/PHPMailer/PHPMailer) <br>
[PHP-JWT](https://github.com/firebase/php-jwt)

## License
Copyright © 2022 [MIT License](https://github.com/Sleon4/Lion-PHP/blob/main/LICENSE)