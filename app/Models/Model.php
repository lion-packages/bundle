<?php

namespace App\Models;

use App\Http\Request\Request;
use LionSql\Drivers\MySQLDriver as Builder;

class Model {

	protected object $env;
	
	public function __construct() {
		
	}

	public function init(array $config = []): void {
		$this->env = Request::getInstance()->env();

		Builder::init([
			'host' => isset($config['host']) ? $config['host'] : $this->env->DB_HOST,
			'db_name' => isset($config['db_name']) ? $config['db_name'] : $this->env->DB_NAME,
			'user' => isset($config['user']) ? $config['user'] : $this->env->DB_USER,
			'password' => isset($config['password']) ? $config['password'] : $this->env->DB_PASSWORD,
			'charset' => isset($config['charset']) ? $config['charset'] : $this->env->DB_CHARSET
		]);
	}

}