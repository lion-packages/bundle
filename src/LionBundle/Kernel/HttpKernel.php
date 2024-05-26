<?php

declare(strict_types=1);

namespace Lion\Bundle\Kernel;

use Lion\Bundle\Exceptions\RulesException;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Helpers\Rules;
use Lion\Bundle\Interface\RulesInterface;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Request\Status;

/**
 * Kernel for HTTP requests
 *
 * @property Container $container [Container to generate dependency injection]
 *
 * @package Lion\Bundle\Kernel
 */
class HttpKernel
{
    /**
     * [Container to generate dependency injection]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * @required
     */
    public function setContainer(Container $container): HttpKernel
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Check URL patterns to validate if a URL matches or is identical
     *
     * @param string $uri [API URI]
     *
     * @return bool
     */
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

    /**
     * Check for errors with the defined rules
     *
     * @return void
     *
     * @throws RulesException [If there are rule errors]
     */
    public function validateRules(): void
    {
        $errors = [];

        $allRules = Routes::getRules();

        if (isset($allRules[$_SERVER['REQUEST_METHOD']])) {
            /** @var array<int, RulesInterface> $rules */
            foreach ($allRules[$_SERVER['REQUEST_METHOD']] as $uri => $rules) {
                if ($this->checkUrl($uri)) {
                    foreach ($rules as $rule) {
                        /** @var RulesInterface|Rules $ruleClass */
                        $ruleClass = $this->container->injectDependencies(new $rule());

                        $ruleClass->passes();

                        $ruleKey = array_keys($ruleClass->getErrors());

                        $ruleErrors = array_values($ruleClass->getErrors());

                        if (count($ruleErrors) > 0) {
                            $errors[reset($ruleKey)] = reset($ruleErrors);
                        }
                    }
                }
            }

            if (count($errors) > 0) {
                throw new RulesException('parameter error', Status::RULE_ERROR, Http::INTERNAL_SERVER_ERROR, [
                    'rules-error' => $errors
                ]);
            }
        }
    }
}
