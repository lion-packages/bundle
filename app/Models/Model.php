<?php

namespace App\Models;

use LionSql\Sql\QueryBuilder as Builder;

class Model {
	
	public function __construct() {
		
	}

	public function init(): void {
		Builder::connect([
			'host' => $_ENV['DB_HOST'],
			'db_name' => $_ENV['DB_NAME'],
			'user' => $_ENV['DB_USER'],
			'password' => $_ENV['DB_PASSWORD'],
			'charset' => $_ENV['DB_CHARSET']
		]);
	}

}