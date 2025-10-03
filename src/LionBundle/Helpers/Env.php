<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Lion\Request\Http;
use RuntimeException;

/**
 * Environment variable manager with support for isolated sandbox environments.
 *
 * This class provides access to environment variables through a unified API.
 * It also supports enabling multiple "sandbox" environments, each identified
 * by a unique context ID. Sandboxes are useful for running tests in isolation
 * without affecting the global $_ENV state or other concurrent sandboxes.
 *
 * Usage:
 * - Call enableSandbox($id) to create and switch into a sandbox with the given ID.
 * - Call useSandbox($id) to switch between sandboxes.
 * - Call disableSandbox($id) to remove a sandbox and restore global context if active.
 *
 * The implementation ensures tests or routines can run in parallel without
 * interfering with each other's environment variables.
 */
class Env
{
    /**
     * Stores all active sandbox environments.
     *
     * @var array<string, array<string, string|int|float|bool|null>>
     */
    private static array $sandboxes = [];

    /**
     * The ID of the currently active sandbox, if any.
     *
     * @var string|null
     */
    private static ?string $activeContext = null;

    /**
     * Checks whether a sandbox is currently active.
     *
     * @return bool True if a sandbox is active, false otherwise.
     */
    public static function isSandboxActive(): bool
    {
        return self::$activeContext !== null;
    }

    /**
     * Gets the value of an environment variable.
     *
     * If a sandbox is active, the lookup is performed against the active sandbox.
     * Otherwise, the value is retrieved from the global $_ENV array.
     *
     * @param string $key The name of the environment variable.
     * @param string|int|float|bool|null $default Default value returned if not set.
     *
     * @return string|int|float|bool|null The environment variable value or default.
     */
    public static function get(string $key, string|int|float|bool|null $default = null): string|int|float|bool|null
    {
        return self::getOption($key, $default);
    }

    /**
     * Sets the value of an environment variable.
     *
     * If a sandbox is active, the variable is stored only in that sandbox.
     * Otherwise, it is stored in the global $_ENV array.
     *
     * @param string $key The environment variable name.
     * @param string|int|float|bool|null $value The value to assign.
     *
     * @return void
     */
    public static function set(string $key, string|int|float|bool|null $value): void
    {
        if (self::isSandboxActive()) {
            self::$sandboxes[self::$activeContext][$key] = $value;
        }
    }

    /**
     * Enables a sandbox environment for the given context ID.
     *
     * @param string $contextId Unique identifier for the sandbox.
     *
     * @throws RuntimeException If a sandbox with the given ID already exists.
     *
     * @return void
     */
    public static function enableSandbox(string $contextId): void
    {
        if (isset(self::$sandboxes[$contextId])) {
            throw new RuntimeException("Sandbox '{$contextId}' already exists.", Http::INTERNAL_SERVER_ERROR);
        }

        /** @phpstan-ignore-next-line */
        self::$sandboxes[$contextId] = $_ENV;

        self::$activeContext = $contextId;
    }

    /**
     * Disables a sandbox environment and removes it from the active list.
     *
     * If the disabled sandbox was active, the context switches back to null.
     *
     * @param string $contextId The ID of the sandbox to disable.
     *
     * @return void
     */
    public static function disableSandbox(string $contextId): void
    {
        unset(self::$sandboxes[$contextId]);

        if (self::$activeContext === $contextId) {
            self::$activeContext = null;
        }
    }

    /**
     * Internal value resolver with type transformation.
     *
     * Supports automatic transformations:
     * - "true" or "(true)" → true
     * - "false" or "(false)" → false
     * - "empty" or "(empty)" → ""
     * - "null" or "(null)" → null
     *
     * Quoted values like "'foo'" or "\"bar\"" are unwrapped to "foo" or "bar".
     *
     * @param string $key The environment variable name.
     * @param string|int|float|bool|null $default Default value if not set.
     *
     * @return string|int|float|bool|null The resolved value.
     */
    private static function getOption(string $key, string|int|float|bool|null $default): string|int|float|bool|null
    {
        $source = self::$activeContext !== null
            ? self::$sandboxes[self::$activeContext]
            : $_ENV;

        $transform = static function (string|int|float|bool|null $value): string|int|float|bool|null {
            return match ($value) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $value,
            };
        };

        if (!array_key_exists($key, $source)) {
            return $transform($default);
        }

        /** @var string|int|float|bool|null $value */
        $value = $source[$key];

        $valueTransform = $transform($value);

        if (is_string($valueTransform) && preg_match('/\A([\'"])(.*)\1\z/', $valueTransform, $matches)) {
            return $matches[2];
        }

        return $valueTransform;
    }
}
