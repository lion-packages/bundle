<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use App\Enums\Framework\StatusResponseEnum;
use \Closure;
use LionSecurity\Validation;

trait ShowErrors
{
    private static array $validation;

    public static function validate(Closure $validate_function): void
    {
        $response = Validation::validate((array) request, $validate_function);
        self::$validation = isError($response) ? $response->messages : [];
    }

    public static function display(): void
    {
        if (count(self::$validation) > 0) {
            foreach (self::$validation as $keyErrors => $errors) {
                logger($errors[0], StatusResponseEnum::ERROR->value);
                finish(error($errors[0]));
            }
        }
    }
}
