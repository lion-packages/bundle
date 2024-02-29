<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\AESCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class AESTest extends Test
{
    const OUTPUT_MESSAGE = 'Keys created successfully';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new AESCommand()));
        $this->commandTester = new CommandTester($application->find('new:aes'));
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
