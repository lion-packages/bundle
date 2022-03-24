<?php

namespace App\Models;

use LionSql\QueryBuilder as Builder;

class Model {
	
	public function __construct() {
		
	}

	public function init(array $config = []): void {
		Builder::connect([
			'host' => isset($config['host']) ? $config['host'] : $_ENV['DB_HOST'],
			'db_name' => isset($config['db_name']) ? $config['db_name'] : $_ENV['DB_NAME'],
			'user' => isset($config['user']) ? $config['user'] : $_ENV['DB_USER'],
			'password' => isset($config['password']) ? $config['password'] : $_ENV['DB_PASSWORD'],
			'charset' => isset($config['charset']) ? $config['charset'] : $_ENV['DB_CHARSET']
		]);
	}

}