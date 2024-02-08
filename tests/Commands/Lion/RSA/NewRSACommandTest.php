<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\RSA;

use Lion\Bundle\Commands\Lion\RSA\NewRSACommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NewRSACommandTest extends Test
{
    const URL_PATH = 'keys/';
    const OUTPUT_MESSAGE = 'public and private';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NewRSACommand()));
        $this->commandTester = new CommandTester($application->find('rsa:new'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./storage/keys/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['--path' => self::URL_PATH]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists('./storage/' . self::URL_PATH . 'public.key');
        $this->assertFileExists('./storage/' . self::URL_PATH . 'private.key');
    }
}
