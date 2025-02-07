<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Route\RouteListCommand;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Middleware;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RouteListCommandTest extends Test
{
    private CommandTester $commandTester;
    private RouteListCommand $routeListCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var RouteListCommand $routeListCommand */
        $routeListCommand = new Container()->resolve(RouteListCommand::class);

        $this->routeListCommand = $routeListCommand;

        $application = new Application();

        $application->add($this->routeListCommand);

        $this->commandTester = new CommandTester($application->find('route:list'));

        $this->initReflection($this->routeListCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(RouteListCommand::class, $this->routeListCommand->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(RouteListCommand::class, $this->routeListCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    #[Testing]
    public function execute(): void
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
