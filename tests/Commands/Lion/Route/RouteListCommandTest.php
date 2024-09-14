<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Lion\Bundle\Commands\Lion\Route\RouteListCommand;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Route\Middleware;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class RouteListCommandTest extends Test
{
    private const string OUTPUT_MESSAGE = 'ROUTES';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $container = new Container();

        $application = (new Kernel())
            ->getApplication();

        $application->add($container->injectDependencies(new RouteListCommand()));

        $this->commandTester = new CommandTester($application->find('route:list'));
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
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(RouteMiddleware::class, $display);
    }
}
