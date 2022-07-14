<?php

namespace App\Models;

class Model {

	protected object $env;

	private function __construct() {

	}

    protected function init(): void {
        $this->env = \LionRequest\Request::getInstance()->env();
    }

}