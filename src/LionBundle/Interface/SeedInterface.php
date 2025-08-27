<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use stdClass;

/**
 * Implement abstract methods for seeds.
 */
interface SeedInterface
{
    /**
     * Seed the application's database.
     *
     * @return int|stdClass
     **/
    public function run(): int|stdClass;
}
