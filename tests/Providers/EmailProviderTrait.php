<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Mailer\Mailer;

trait EmailProviderTrait
{
    public function runMailer(): void
    {
        Mailer::initialize([
            $_ENV['MAIL_NAME'] => [
                'name' => $_ENV['MAIL_NAME'],
                'type' => $_ENV['MAIL_TYPE'],
                'host' => $_ENV['MAIL_HOST'],
                'username' => $_ENV['MAIL_USER_NAME'],
                'password' => $_ENV['MAIL_PASSWORD'],
                'port' => (int) $_ENV['MAIL_PORT'],
                'encryption' => $_ENV['MAIL_ENCRYPTION'],
                'debug' => (bool) $_ENV['MAIL_DEBUG']
            ],
            $_ENV['MAIL_NAME_SUPP'] => [
                'name' => $_ENV['MAIL_NAME_SUPP'],
                'type' => $_ENV['MAIL_TYPE_SUPP'],
                'host' => $_ENV['MAIL_HOST_SUPP'],
                'username' => $_ENV['MAIL_USER_NAME_SUPP'],
                'password' => $_ENV['MAIL_PASSWORD_SUPP'],
                'port' => (int) $_ENV['MAIL_PORT_SUPP'],
                'encryption' => $_ENV['MAIL_ENCRYPTION_SUPP'],
                'debug' => (bool) $_ENV['MAIL_DEBUG_SUPP']
            ],
        ], $_ENV['MAIL_NAME']);
    }
}
