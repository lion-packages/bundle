<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Implement abstract methods for seeds
 *
 * @package Lion\Bundle\Interface
 */
interface SeedInterface
{
    /**
     * Seed the application's database
     *
     * @return object
     **/
    public function run(): object;
}
