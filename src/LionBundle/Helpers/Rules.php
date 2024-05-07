<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Closure;
use Lion\Request\Request;
use Lion\Security\Validation;

/**
 * Define the rules and execute their validations
 *
 * @property Validation $validation [Validation class object]
 * @property Request $request [Allows you to obtain data captured in an HTTP
 * request and modify headers]
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
     * Allows you to obtain data captured in an HTTP request and modify headers
     *
     * @var Request $request
     */
    private Request $request;

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
     * @required
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
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
        $response = $this->validation->validate((array) $this->request->capture(), $validateFunction);

        $this->responses = isError($response) ? $response->messages : [];
    }

    /**
     * Gets the list of rule errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->responses;
    }
}
