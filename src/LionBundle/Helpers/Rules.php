<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Closure;
use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Security\Validation;

class Rules
{
    protected array $validation;

    public function validate(Closure $validate_function): void
    {
        $response = (new Validation())->validate((array) request, $validate_function);
        $this->validation = isError($response) ? $response->messages : [];
    }

    public function display(): void
    {
        foreach ($this->validation as $errors) {
            logger($errors[0], StatusResponseEnum::ERROR->value);
            finish(error($errors[0]));
        }
    }
}
