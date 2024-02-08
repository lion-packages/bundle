<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\AES;

use Lion\Bundle\Commands\Lion\AES\NewAESCommand;
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
        $application->add((new Container())->injectDependencies(new NewAESCommand()));
        $this->commandTester = new CommandTester($application->find('aes:new'));
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
