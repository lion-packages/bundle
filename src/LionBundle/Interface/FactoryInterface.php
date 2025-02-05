<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Implement abstract methods for factories
 *
 * @package Lion\Bundle\Interface
 */
interface FactoryInterface
{
    /**
     * Defines the factory columns
     *
     * @return array<int, string>
     */
    public static function columns(): array;

    /**
     * Define the model's default state
     *
     * @return array<int|string, mixed>
     **/
    public static function definition(): array;
}
