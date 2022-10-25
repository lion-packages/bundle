<?php

namespace App\Traits\Framework;

trait Singleton {

    private static $singleton = false;

    final private function __construct() {
        $this->init();
    }

    final public static function getInstance() {
        if (self::$singleton === false) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    protected function init() {}

}