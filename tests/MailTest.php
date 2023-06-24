<?php

namespace Tests;

use LionMailer\Services\PHPMailer\Mail as PHPMail;
use LionMailer\Services\Symfony\Mail as SymfMail;
use PHPUnit\Framework\TestCase;

class MailTest extends TestCase {

    public function setUp(): void {
        (\Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();

        \LionMailer\MailService::run([
            'default' => $_ENV['APP_ENV'],
            'accounts' => [
                $_ENV['APP_ENV'] => [
                    'services' => ["symfony", "phpmailer"],
                    'debug' => $_ENV['MAIL_DEBUG'],
                    'host' => $_ENV['MAIL_HOST'],
                    'encryption' => $_ENV['MAIL_ENCRYPTION'],
                    'port' => $_ENV['MAIL_PORT'],
                    'name' => $_ENV['MAIL_NAME'],
                    'account' => $_ENV['MAIL_ACCOUNT'],
                    'password' => $_ENV['MAIL_PASSWORD']
                ]
            ],
        ]);
    }

    public function testPHPMailer() {
        $response = PHPMail::address($_ENV['MAIL_ACCOUNT'], $_ENV['MAIL_NAME'])
            ->subject("TESTING")
            ->body("<h1>TESTING</h1>")
            ->send();

        $this->assertEquals("success", $response->status);
    }

    public function testSymfonyMailer() {
        $response = SymfMail::address($_ENV['MAIL_ACCOUNT'], $_ENV['MAIL_NAME'])
            ->subject("TESTING")
            ->body("<h1>TESTING</h1>")
            ->send();

        $this->assertEquals("success", $response->status);
    }

}
