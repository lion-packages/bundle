<?php

declare(strict_types=1);

namespace Tests\Commands\New;

use LionBundle\Commands\New\InterfaceCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;

class InterfaceCommandTest extends Test
{
    const URL_PATH = './app/Interfaces/';
    const CLASS_NAME = 'TestInterface';
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'interface has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new InterfaceCommand()));
        $this->commandTester = new CommandTester($application->find('new:interface'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['interface' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
