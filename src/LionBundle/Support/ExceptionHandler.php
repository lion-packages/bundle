<?php

declare(strict_types=1);

namespace Lion\Bundle\Support;

use Closure;

/**
 * Initializes configuration data for serializing exceptions
 *
 * @package Lion\Bundle\Support
 */
class ExceptionHandler
{
    /**
     * @var array{
     *     addInformation: bool,
     *     callback: Closure|null
     * } $options Configuration data for serializing exceptions
     */
    private static array $options;

    /**
     * Defines configuration data for serializing exceptions
     *
     * @param array{
     *     addInformation: bool,
     *     callback: Closure|null
     * } $options Configuration data for serializing exceptions
     *
     * @return void
     */
    public static function handle(array $options): void
    {
        self::$options = $options;
    }

    /**
     * Returns configuration data for serializing exceptions
     *
     * @return array{
     *     addInformation: bool,
     *     callback: Closure|null
     * }
     */
    public static function getOptions(): array
    {
        return self::$options;
    }
}
