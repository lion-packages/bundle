<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Helpers\Arr;
use Lion\Route\Attributes\Rules;
use stdClass;

class ExampleProvider
{
    public function myMethod(): void
    {
    }

    public function getArrExample(Arr $arr): array
    {
        return $arr->of(['message' => 'testing'])->get();
    }

    #[Rules(NameProviderRule::class)]
    public function getResult(): stdClass
    {
        return success('Name: ' . request('name'));
    }
}
