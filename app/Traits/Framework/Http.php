<?php

declare(strict_types=1);

namespace App\Traits\Framework;

trait Http
{
    public function checkUrl(string $uri): bool
    {
        $clean_request_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $array_uri = explode('/', $uri);
        $arrayurl = explode('/', $clean_request_uri);

        foreach ($array_uri as $index => &$value) {
            if (preg_match('/^\{.*\}$/', $value)) {
                $value = 'dynamic-param';
                $arrayurl[$index] = 'dynamic-param';
            }
        }

        return implode('/', $array_uri) === implode('/', $arrayurl);
    }
}
