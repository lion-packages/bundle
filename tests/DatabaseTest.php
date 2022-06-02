<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use LionSQL\Drivers\MySQLDriver as Builder;

class DatabaseTest extends TestCase {

    public function setUp(): void {
        (\Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();
    }

    public function testConnection(): void {
        $responseConnect = Builder::init([
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'db_name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => $_ENV['DB_CHARSET']
        ]);

        $this->assertEquals('success', $responseConnect->status);
    }

}