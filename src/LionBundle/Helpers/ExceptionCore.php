<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use JsonSerializable;
use Lion\Request\Request;
use Throwable;

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
        set_exception_handler(function (Throwable $exception) {
            if ($exception instanceof JsonSerializable) {
                die(json_encode($exception, JSON_PRETTY_PRINT));
            }

            $error = error(
                $exception->getMessage(),
                (0 === $exception->getCode() ? Request::HTTP_INTERNAL_SERVER_ERROR : $exception->getCode()),
                [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ]
            );

            die(json_encode($error, JSON_PRETTY_PRINT));
        });
    }
}
