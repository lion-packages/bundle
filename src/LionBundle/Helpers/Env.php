<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

/**
 * Environment variable manager.
 */
class Env
{
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key The name of the environment variable.
     * @param string|int|float|bool|null $default The default value if the
     * environment variable is not set.
     *
     * @return string|int|float|bool|null The value of the environment variable, or
     * the default value if not found.
     */
    public static function get(string $key, string|int|float|bool|null $default = null): string|int|float|bool|null
    {
        return self::getOption($key, $default);
    }


    /**
     * Gets the environment variable name associated with a given value.
     *
     * @param string $value The value to search for within the environment
     * variables.
     *
     * @return string|int|false The variable name if found, or false if not found.
     */
    public static function getKey(string $value): string|int|false
    {
        return array_search($value, $_ENV, true);
    }

    /**
     * Retrieves and transforms the value of an environment variable.
     *
     * Possible transformations:
     * - "true" or "(true)" → true
     * - "false" or "(false)" → false
     * - "empty" or "(empty)" → ""
     * - "null" or "(null)" → null
     *
     * @param string $key The name of the environment variable.
     * @param string|int|float|bool|null $default The default value if the
     * environment variable is not set.
     *
     * @return string|int|float|bool|null The transformed environment variable
     * value, or the default value if not found.
     */
    private static function getOption(string $key, string|int|float|bool|null $default): string|int|float|bool|null
    {
        /** @phpstan-ignore-next-line */
        if (!isset($_ENV)) {
            return null;
        }

        $transform = static function (string|int|float|bool|null $value): string|int|float|bool|null {
            return match ($value) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $value,
            };
        };

        if (!array_key_exists($key, $_ENV)) {
            return $transform($default);
        }

        /** @var string $value */
        $value = $_ENV[$key];

        $valueTransform = $transform($value);

        if (is_string($valueTransform) && preg_match('/\A([\'"])(.*)\1\z/', $valueTransform, $matches)) {
            return $matches[2];
        }

        return $valueTransform;
    }
}
