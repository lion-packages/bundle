<?php

declare(strict_types=1);

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Helpers\Rules;
use Lion\Bundle\Interface\RulesInterface;
use Lion\Route\Route;
use Valitron\Validator;

/**
 * -----------------------------------------------------------------------------
 * Rules
 * -----------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * -----------------------------------------------------------------------------
 **/

$customRule = new class extends Rules implements RulesInterface
{
    public string $field = 'name';

    public string $desc = '';

    public string $value = '';

    public bool $disabled = false;

    public function passes(): void
    {
        $this->validate(function (Validator $validator): void {
            $validator
                ->rule('optional', $this->field)
                ->message('the "name" property is optional');
        });
    }
};

Routes::setRules([
    Route::GET => [],
    Route::POST => [
        '/api/test' => [
            $customRule::class,
        ]
    ],
]);
