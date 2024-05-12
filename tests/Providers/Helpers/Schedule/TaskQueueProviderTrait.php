<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Schedule;

trait TaskQueueProviderTrait
{
    public static function addProvider(): array
    {
        return [
            [
                'callable' => function (object $object): object {
                    return $object;
                }
            ],
            [
                'callable' => ['ExampleClass', 'exampleMethod']
            ]
        ];
    }
}
