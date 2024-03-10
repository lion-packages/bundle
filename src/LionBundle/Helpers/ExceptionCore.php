<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Exception;

/**
 * Manage exceptions defined in the system
 *
 * @package Lion\Bundle\Helpers
 */
class ExceptionCore
{
    /**
     * Manages exceptions and serializes them to JSON format
     *
     * @return void
     */
    public function exceptionHandler(): void
    {
        set_exception_handler(function(Exception $e) {
            die(json_encode($e, JSON_PRETTY_PRINT));
        });
    }
}
