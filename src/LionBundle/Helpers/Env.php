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
     * @param  string $key [Property name]
     * @param  mixed $default [Default value]
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key, $default);
    }

    /**
     * Gets and transforms the possible value of environment variables
     *
     * @param  string $key [Property name]
     * @param  mixed $default [Default value]
     *
     * @return mixed
     */
    private static function getOption(string $key, mixed $default): mixed
    {
        $transform = function(mixed $value): mixed {
            switch ($value) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'empty':
                case '(empty)':
                    return '';
                case 'null':
                case '(null)':
                    return null;
                default:
                    return $value;
            }
        };

        if (empty($_ENV[$key])) {
            return $transform($default);
        }

        $value = $transform($_ENV[$key]);

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return $value;
    }
}
