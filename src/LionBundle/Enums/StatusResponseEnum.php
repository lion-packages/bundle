<?php

declare(strict_types=1);

namespace Lion\Bundle\Enums;

use Lion\Request\Response;

/**
 * It has enabled different types of response and functions that complement its
 * function
 *
 * @package Lion\Bundle\Enums
 */
enum StatusResponseEnum: string
{
    /**
     * [Represents a correct response object]
     */
    case SUCCESS = Response::SUCCESS;

    /**
     * [Represents an error response object]
     */
    case ERROR = Response::ERROR;

    /**
     * [Represents a warning response object]
     */
    case WARNING = Response::WARNING;

    /**
     * [Represents an information response object]
     */
    case INFO = Response::INFO;

    /**
     * [Represents a database error response object]
     */
    case DATABASE_ERROR = Response::DATABASE_ERROR;

    /**
     * [Represents a sesion error response object]
     */
    case SESSION_ERROR = Response::SESSION_ERROR;

    /**
     * [Represents a route error response object]
     */
    case ROUTE_ERROR = Response::ROUTE_ERROR;

    /**
     * [Represents a file error response object]
     */
    case FILE_ERROR = Response::FILE_ERROR;

    /**
     * [Represents a mail error response object]
     */
    case MAIL_ERROR = Response::MAIL_ERROR;

    /**
     * [Represents a rules error response object]
     */
    case RULE_ERROR = 'rule-error';

    /**
     * Return a list with the different types of responses available
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (object $value) => $value->value, self::cases());
    }

    /**
     * Return a list of available errors
     *
     * @return array<int, string>
     */
    public static function errors(): array
    {
        return [...(new Response)->getErrors(), self::RULE_ERROR->value];
    }
}
