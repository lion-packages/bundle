<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use Faker\Factory;

trait Faker
{
    private static function get()
    {
        return Factory::create();
    }
}
