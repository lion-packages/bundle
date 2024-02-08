<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

use Faker\Factory;
use Faker\Generator;

trait Migration
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }
}
