<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Mailer\Mailer;

trait EmailProviderTrait
{
    public function runMailer(): void
    {
        Mailer::initialize([
            env->MAIL_NAME => [
                'name' => env->MAIL_NAME,
                'type' => env->MAIL_TYPE,
                'host' => env->MAIL_HOST,
                'username' => env->MAIL_USER_NAME,
                'password' => env->MAIL_PASSWORD,
                'port' => (int) env->MAIL_PORT,
                'encryption' => env->MAIL_ENCRYPTION,
                'debug' => (bool) env->MAIL_DEBUG
            ],
            env->MAIL_NAME_SUPP => [
                'name' => env->MAIL_NAME_SUPP,
                'type' => env->MAIL_TYPE_SUPP,
                'host' => env->MAIL_HOST_SUPP,
                'username' => env->MAIL_USER_NAME_SUPP,
                'password' => env->MAIL_PASSWORD_SUPP,
                'port' => (int) env->MAIL_PORT_SUPP,
                'encryption' => env->MAIL_ENCRYPTION_SUPP,
                'debug' => (bool) env->MAIL_DEBUG_SUPP
            ]
        ], env->MAIL_NAME);
    }
}
