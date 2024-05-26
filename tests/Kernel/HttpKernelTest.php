<?php

declare(strict_types=1);

namespace Tests\Kernel;

use Lion\Bundle\Exceptions\RulesException;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Helpers\Rules;
use Lion\Bundle\Interface\RulesInterface;
use Lion\Bundle\Kernel\HttpKernel;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\Kernel\HttpKernelProviderTrait;

class HttpKernelTest extends Test
{
    use HttpKernelProviderTrait;

    const MESSAGE = 'parameter error';
    const RULE = "App\\Rules\\TestRule";
    const URI = '/api/test';

    private HttpKernel $httpKernel;

    protected function setUp(): void
    {
        $this->httpKernel = (new Container())->injectDependencies(new HttpKernel());
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI']);

        unset($_SERVER['REQUEST_METHOD']);

        $this->rmdirRecursively('./app/');
    }

    #[DataProvider('checkUrlProvider')]
    public function testCheckUrl(string $requestUri, string $uri, bool $response): void
    {
        $_SERVER['REQUEST_URI'] = $requestUri;

        $this->assertSame($response, $this->httpKernel->checkUrl($uri));
    }

    public function testValidateRules(): void
    {
        (new Kernel())
            ->execute('php lion new:rule TestRule', false);

        $this->assertFileExists('./app/Rules/TestRule.php');

        /** @var Rules|RulesInterface $rule */
        $rule = new (self::RULE);

        $this->assertInstances($rule, [
            Rules::class,
            RulesInterface::class,
            self::RULE
        ]);

        $this
            ->exception(RulesException::class)
            ->exceptionMessage(self::MESSAGE)
            ->exceptionStatus(Status::RULE_ERROR)
            ->exceptionCode(Http::INTERNAL_SERVER_ERROR)
            ->expectLionException(function () use ($rule): void {
                $rules = Routes::getRules();

                $rules[Http::POST][self::URI][] = $rule::class;

                Routes::setRules($rules);

                $_SERVER['REQUEST_METHOD'] = Http::POST;

                $_SERVER['REQUEST_URI'] = self::URI;

                $this->httpKernel->validateRules();
            });
    }
}
