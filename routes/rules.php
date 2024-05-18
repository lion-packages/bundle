<?php

declare(strict_types=1);

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Route\Route;
use Tests\Providers\Helpers\Commands\RulesProvider;

/**
 * -----------------------------------------------------------------------------
 * Rules
 * -----------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * -----------------------------------------------------------------------------
 **/

Routes::setRules([
    Route::GET => [],
    Route::POST => [
        '/api/test' => [
            RulesProvider::class
        ]
    ],
]);
