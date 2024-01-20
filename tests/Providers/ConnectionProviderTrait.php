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
                ]
            ]
        ]);
    }
}
