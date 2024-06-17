<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

/**
 * Environment variable manager
 *
 * @package Lion\Bundle\Helpers
 */
class Env
{
    /**
     * Gets the value defined for an environment variable
     *
     * @param string $key [Property name]
     * @param mixed $default [Default value]
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key, $default);
    }

    /**
     * Gets the name of the property defined by its value
     *
     * @param string $value [Property value]
     *
     * @return string|int|false
     */
    public static function getKey(string $value): string|int|false
    {
        return array_search($value, $_ENV);
    }

    /**
     * Gets and transforms the possible value of environment variables
     *
     * @param string $key [Property name]
     * @param mixed $default [Default value]
     *
     * @return mixed
     */
    private static function getOption(string $key, mixed $default): mixed
    {
        $transform = function (mixed $value): mixed {
            return match ($value) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $value,
            };
        };

        if (empty($_ENV[$key])) {
            return $transform($default);
        }

        $value = $transform($_ENV[$key]);

        if (is_string($value) && preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return $value;
    }
}
