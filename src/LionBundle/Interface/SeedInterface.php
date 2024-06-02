<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use stdClass;

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
     * @return stdClass
     **/
    public function run(): stdClass;
}
