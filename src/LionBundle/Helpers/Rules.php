<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Closure;
use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Security\Validation;

abstract class Rules
{
    private Validation $validation;

    protected array $responses;

    /**
     * @required
     */
    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
    }

    protected function validate(Closure $validateFunction): void
    {
        $response = $this->validation->validate((array) request, $validateFunction);

        $this->responses = isError($response) ? $response->messages : [];
    }

    public function display(): void
    {
        foreach ($this->responses as $errors) {
            logger($errors[0], StatusResponseEnum::ERROR->value);

            finish(error($errors[0]));
        }
    }
}
