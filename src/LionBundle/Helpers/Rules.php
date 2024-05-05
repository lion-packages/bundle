<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Closure;
use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Security\Validation;

/**
 * Define the rules and execute their validations
 *
 * @property Validation $validation [Validation class object]
 * @property array $responses [Array containing all answers]
 *
 * @package Lion\Bundle\Helpers
 */
abstract class Rules
{
    /**
     * [Validation class object]
     *
     * @var Validation $validation
     */
    private Validation $validation;

    /**
     * [Array containing all answers]
     *
     * @var array $responses
     */
    protected array $responses;

    /**
     * @required
     */
    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * Executes the validation of the Validate object of Validator
     *
     * @param Closure $validateFunction [Function that executes the rules
     * defined in the Validator object]
     *
     * @return void
     */
    protected function validate(Closure $validateFunction): void
    {
        $response = $this->validation->validate((array) request, $validateFunction);

        $this->responses = isError($response) ? $response->messages : [];
    }

    /**
     * Shows the available error responses and adds them to the log record
     *
     * @return void
     */
    public function display(): void
    {
        foreach ($this->responses as $errors) {
            logger($errors[0], StatusResponseEnum::ERROR);

            finish(error($errors[0]));
        }
    }
}
