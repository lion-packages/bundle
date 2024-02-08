<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Lion\Bundle\Commands\Lion\Route\RouteListCommand;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Route\Route;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\EnviromentProviderTrait;

class RouteListCommandTest extends Test
{
    use EnviromentProviderTrait;

    const OUTPUT_MESSAGE = 'ROUTES';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $application = (new Kernel)->getApplication();
        $application->add((new Container)->injectDependencies(new RouteListCommand()));
        $this->commandTester = new CommandTester($application->find('route:list'));
    }

    public function testExecute(): void
    {
        Routes::setRules([Route::POST => []]);
        Routes::setMiddleware(['app' => []]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
