<?php

namespace App\Models;

use LionRequest\Response;

class Model {

	protected object $env;

	private function __construct() {

	}

    private function processOutput($response): void {
        echo(json_encode($response));
        exit();
    }

    protected function init(array $config = []): void {
        $this->env = \LionRequest\Request::getInstance()->env();

        $response_conn = \LionSQL\Drivers\MySQLDriver::init([
            'host' => isset($config['host']) ? $config['host'] : $this->env->DB_HOST,
            'port' => isset($config['port']) ? $config['port'] : $this->env->DB_PORT,
            'db_name' => isset($config['db_name']) ? $config['db_name'] : $this->env->DB_NAME,
            'user' => isset($config['user']) ? $config['user'] : $this->env->DB_USER,
            'password' => isset($config['password']) ? $config['password'] : $this->env->DB_PASSWORD,
            'charset' => isset($config['charset']) ? $config['charset'] : $this->env->DB_CHARSET
        ]);

        if ($response_conn->status === 'error') {
            $this->processOutput(Response::error($response_conn->message));
        }
    }

}