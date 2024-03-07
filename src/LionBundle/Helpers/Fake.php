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
     * [Generator class object]
     *
     * @var Generator|null $generator
     */
    private static ?Generator $generator = null;

    /**
     * Function that generates a Generator object to obtain fake data
     *
     * @param  string $locale [Regional configuration]
     *
     * @return Generator
     */
    public static function get(string $locale = Factory::DEFAULT_LOCALE): Generator
    {
        if (null === self::$generator) {
            self::$generator = Factory::create($locale);
        }

        return self::$generator;
    }
}
