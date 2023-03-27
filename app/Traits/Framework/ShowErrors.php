<?php

namespace App\Traits\Framework;

use \Closure;
use LionSecurity\Validation;

trait ShowErrors {

    private static array $validation;

    public static function validate(Closure $validate_function): void {
        $response = Validation::validate((array) request, $validate_function);
        self::$validation = $response->status === 'error' ? $response->messages : [];
    }

    public static function display(): void {
        if (count(self::$validation) > 0) {
            foreach (self::$validation as $keyErrors => $errors) {
                logger($errors[0], 'error');

                response->finish(response->error($errors[0], [
                    'fields' => self::$validation,
                ]));
            }
        }
    }

}