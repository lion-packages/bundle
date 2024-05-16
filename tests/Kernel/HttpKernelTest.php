<?php

declare(strict_types=1);

namespace Tests\Kernel;

use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Exceptions\RulesException;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Kernel\HttpKernel;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Request\Request;
use Lion\Request\Status;
use Lion\Route\Route;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\Kernel\HttpKernelProviderTrait;

class HttpKernelTest extends Test
{
    use HttpKernelProviderTrait;

    const URL_PATH = './app/Rules/';
    const NAMESPACE_CLASS = 'App\\Rules\\';
    const CLASS_NAME = 'TestRule';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'rule has been generated';
    const MESSAGE = 'parameter error';

    private HttpKernel $httpKernel;
    private Container $container;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->httpKernel = $this->container->injectDependencies(new HttpKernel());

        $application = (new Kernel())
            ->getApplication();

        $application->add($this->container->injectDependencies(new RulesCommand()));

        $this->commandTester = new CommandTester($application->find('new:rule'));

        $this->createDirectory(self::URL_PATH);
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
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['rule' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = $this->container->injectDependencies(new (self::OBJECT_NAME)());

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);

        $this
            ->exception(RulesException::class)
            ->exceptionMessage(self::MESSAGE)
            ->exceptionStatus(Status::RULE_ERROR)
            ->exceptionCode(Http::HTTP_INTERNAL_SERVER_ERROR)
            ->expectLionException(function (): void {
                $_SERVER['REQUEST_METHOD'] = 'POST';

                $_SERVER['REQUEST_URI'] = 'api/users/1';

                Routes::setRules([
                    Route::POST => [
                        'api/users/{idusers}' => [
                            self::OBJECT_NAME
                        ]
                    ]
                ]);

                $this->httpKernel->validateRules();
            });
    }
}
