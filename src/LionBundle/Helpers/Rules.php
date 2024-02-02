<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Closure;
use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Security\Validation;

abstract class Rules
{
    protected array $validation;

    protected function validate(Closure $validateFunction): void
    {
        $response = (new Validation())->validate((array) request, $validateFunction);
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
