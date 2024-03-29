<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

trait Singleton
{
    private static $singleton = false;

    final private function __construct()
    {
        $this->init();
    }

    final public static function getInstance(): object
    {
        if (self::$singleton === false) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    protected function init(): void
    {

    }
}
