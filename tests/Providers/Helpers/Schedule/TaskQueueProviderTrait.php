<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Schedule;

use stdClass;

trait TaskQueueProviderTrait
{
    public static function addProvider(): array
    {
        return [
            [
                'callable' => function (stdClass $object): stdClass {
                    return $object;
                }
            ],
            [
                'callable' => ['ExampleClass', 'exampleMethod']
            ]
        ];
    }
}
