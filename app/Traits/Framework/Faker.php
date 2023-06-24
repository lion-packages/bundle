<?php

namespace App\Traits\Framework;

use Faker\Factory;

trait Faker {

    private static function get() {
        return Factory::create();
    }

}
