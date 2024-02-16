<?php

declare(strict_types=1);

namespace Lion\Bundle;

use Lion\Bundle\Helpers\Http\Routes;

class HttpKernel
{
    public function checkUrl(string $uri): bool
    {
        $cleanRequestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $arrayUri = explode('/', $uri);
        $arrayUrl = explode('/', $cleanRequestUri);

        foreach ($arrayUri as $index => &$value) {
            if (preg_match('/^\{.*\}$/', $value)) {
                $value = 'dynamic-param';
                $arrayUrl[$index] = 'dynamic-param';
            }
        }

        return implode('/', $arrayUri) === implode('/', $arrayUrl);
    }

    public function validateRules(): void
    {
        $allRules = Routes::getRules();

        if (isset($allRules[$_SERVER['REQUEST_METHOD']])) {
            foreach ($allRules[$_SERVER['REQUEST_METHOD']] as $uri => $rules) {
                if ($this->checkUrl($uri)) {
                    foreach ($rules as $rule) {
                        $ruleClass = new $rule();

                        $ruleClass->passes();
                        $ruleClass->display();
                    }
                }
            }
        }
    }
}
