<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

use Closure;
use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Security\Validation;

trait ShowErrors
{
    private static array $validation;

    public static function validate(Closure $validate_function): void
    {
        $response = (new Validation())->validate((array) request, $validate_function);
        self::$validation = isError($response) ? $response->messages : [];
    }

    public static function display(): void
    {
        if (count(self::$validation) > 0) {
            foreach (self::$validation as $keyErrors => $errors) {
                // logger($errors[0], StatusResponseEnum::ERROR->value);
                // finish(error($errors[0]));
            }
        }
    }
}
