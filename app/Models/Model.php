<?php

namespace App\Models;

class Model {

	protected object $env;

	private function __construct() {

	}

	protected function init(array $config = []): void {
		$this->env = \LionRequest\Request::getInstance()->env();

		\LionSQL\Drivers\MySQLDriver::init([
			'host' => isset($config['host']) ? $config['host'] : $this->env->DB_HOST,
            'port' => isset($config['port']) ? $config['port'] : $this->env->DB_PORT,
			'db_name' => isset($config['db_name']) ? $config['db_name'] : $this->env->DB_NAME,
			'user' => isset($config['user']) ? $config['user'] : $this->env->DB_USER,
			'password' => isset($config['password']) ? $config['password'] : $this->env->DB_PASSWORD,
			'charset' => isset($config['charset']) ? $config['charset'] : $this->env->DB_CHARSET
		]);
	}

}