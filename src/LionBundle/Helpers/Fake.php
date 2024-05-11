<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Faker\Factory;
use Faker\Generator;

/**
 * Create Generator objects with a single instance
 *
 * @package Lion\Bundle\Helpers
 */
class Fake
{
    /**
     * Function that generates a Generator object to obtain fake data
     *
     * @param  string $locale [Regional configuration]
     *
     * @return Generator
     */
    public static function get(string $locale = Factory::DEFAULT_LOCALE): Generator
    {
        return Factory::create($locale);
    }
}
