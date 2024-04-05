<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait MenuCommandProviderTrait
{
    public static function selectedTemplateProvider(): array
    {
        return [
            [
                'output' => '(Vanilla)',
                'inputs' => ["0"],
            ],
            [
                'output' => '(Vue)',
                'inputs' => ["1"],
            ],
            [
                'output' => '(React)',
                'inputs' => ["2"],
            ],
            [
                'output' => '(Preact)',
                'inputs' => ["3"],
            ],
            [
                'output' => '(Lit)',
                'inputs' => ["4"],
            ],
            [
                'output' => '(Svelte)',
                'inputs' => ["5"],
            ],
            [
                'output' => '(Solid)',
                'inputs' => ["6"],
            ],
            [
                'output' => '(Qwik)',
                'inputs' => ["7"],
            ],
            [
                'output' => '(Electron)',
                'inputs' => ["8"],
            ]
        ];
    }
}
