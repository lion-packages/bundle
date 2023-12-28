<?php

declare(strict_types=1);

namespace Tests\Providers;

use LionHelpers\Arr;

class ExampleProvider
{
    public function getArrExample(Arr $arr): array
    {
        return $arr->of(['message' => 'testing'])->get();
    }
}
