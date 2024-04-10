<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\Route\RouteListCommand;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Route\Middleware;
use Lion\Route\Route;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\EnviromentProviderTrait;
use Tests\Providers\ExampleProvider;

class RouteListCommandTest extends Test
{
    use EnviromentProviderTrait;

    const OUTPUT_MESSAGE = 'ROUTES';
    const NAMESPACE_RULE = 'App\\Rules\\UsersNameRule';
    const CLASS_NAME_RULE = 'UsersNameRule';

    private CommandTester $commandTester;
    private CommandTester $commandTesterRule;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $application = (new Kernel)->getApplication();
        $application->add((new Container)->injectDependencies(new RulesCommand()));
        $application->add((new Container)->injectDependencies(new RouteListCommand()));

        $this->commandTester = new CommandTester($application->find('route:list'));
        $this->commandTesterRule = new CommandTester($application->find('new:rule'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
    {
        $listMiddleware = [
            new Middleware('protect-route-list', RouteMiddleware::class, 'protectRouteList')
        ];

        Routes::setMiddleware($listMiddleware);

        $this->assertSame($listMiddleware, Routes::getMiddleware());
        $this->assertSame(Command::SUCCESS, $this->commandTesterRule->execute(['rule' => self::CLASS_NAME_RULE]));

        Routes::setRules([
            Route::POST => [
                '/api/test' => [
                    self::NAMESPACE_RULE
                ]
            ]
        ]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString('UsersNameRule', $display);
        $this->assertStringContainsString('RouteMiddleware', $display);
    }
}
