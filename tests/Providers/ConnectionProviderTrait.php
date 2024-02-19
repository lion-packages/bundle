<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Database\Driver;

trait ConnectionProviderTrait
{
    private function runDatabaseConnections(): void
    {
        Driver::run([
            'default' => env->DB_NAME,
            'connections' => [
                env->DB_NAME => [
                    'type' => env->DB_TYPE,
                    'host' => env->DB_HOST,
                    'port' => env->DB_PORT,
                    'dbname' => env->DB_NAME,
                    'user' => env->DB_USER,
                    'password' => env->DB_PASSWORD
                ],
                env->DB_NAME_TEST => [
                    'type' => env->DB_TYPE_TEST,
                    'host' => env->DB_HOST_TEST,
                    'port' => env->DB_PORT_TEST,
                    'dbname' => env->DB_NAME,
                    'user' => env->DB_USER_TEST,
                    'password' => env->DB_PASSWORD_TEST
                ]
            ]
        ]);
    }
}
