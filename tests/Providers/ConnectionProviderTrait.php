<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Database\Driver;

trait ConnectionProviderTrait
{
    use EnviromentProviderTrait;

    private function runDatabaseConnections(): void
    {
        $this->loadEnviroment();

        Driver::run([
            'default' => $_ENV['DB_NAME'],
            'connections' => [
                $_ENV['DB_NAME'] => [
                    'type' => $_ENV['DB_TYPE'],
                    'host' => $_ENV['DB_HOST'],
                    'port' => $_ENV['DB_PORT'],
                    'dbname' => $_ENV['DB_NAME'],
                    'user' => $_ENV['DB_USER'],
                    'password' => $_ENV['DB_PASSWORD']
                ],
                $_ENV['DB_NAME_TEST'] => [
                    'type' => $_ENV['DB_TYPE_TEST'],
                    'host' => $_ENV['DB_HOST_TEST'],
                    'port' => $_ENV['DB_PORT_TEST'],
                    'dbname' => $_ENV['DB_NAME'],
                    'user' => $_ENV['DB_USER_TEST'],
                    'password' => $_ENV['DB_PASSWORD_TEST']
                ]
            ]
        ]);
    }
}
