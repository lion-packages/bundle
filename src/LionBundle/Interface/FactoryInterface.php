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
     * Define the model's default state
     *
     * @return array
     **/
    public static function definition(): array;
}
