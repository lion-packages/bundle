<?php

declare(strict_types=1);

namespace Lion\Bundle\Enums;

/**
 * Defines the types of LOG that are enabled
 *
 * @package Lion\Bundle\Enums
 */
enum LogTypeEnum: string
{
    /**
     * Adds a log record at the DEBUG level
     */
    case DEBUG = 'debug';

    /**
     * Adds a log record at the INFO level
     */
    case INFO = 'info';

    /**
     * Adds a log record at the NOTICE level
     */
    case NOTICE = 'notice';

    /**
     * Adds a log record at the WARNING level
     */
    case WARNING = 'warning';

    /**
     * Adds a log record at the ERROR level
     */
    case ERROR = 'error';

    /**
     * Adds a log record at the CRITICAL level
     */
    case CRITICAL = 'critical';

    /**
     * Adds a log record at the ALERT level
     */
    case ALERT = 'alert';

    /**
     * Adds a log record at the EMERGENCY level
     */
    case EMERGENCY = 'emergency';
}
