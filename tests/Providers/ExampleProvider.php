<?php

declare(strict_types=1);

namespace Tests\Providers;

use Exception;
use Lion\Helpers\Arr;
use Lion\Request\Http;
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

    /**
     * Execute a sample exception.
     *
     * @return void
     *
     * @throws Exception
     */
    public function generateError(): void
    {
        throw new Exception('ERR', Http::INTERNAL_SERVER_ERROR);
    }
}
