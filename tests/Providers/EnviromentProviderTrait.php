<?php

declare(strict_types=1);

namespace Tests\Providers;

use Dotenv\Dotenv;

trait EnviromentProviderTrait
{
    private function loadEnviroment(): void
    {
        Dotenv::createImmutable(__DIR__ . '/../../')->load();
    }
}
