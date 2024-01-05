<?php

declare(strict_types=1);

namespace Tests\Commands\New;

use LionBundle\Commands\New\CommandsCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;

class CommandsCommandTest extends Test
{
    const URL_PATH = './app/Console/Commands/';
    const NAMESPACE_CLASS = 'App\\Console\\Commands\\';
    const CLASS_NAME = 'TestCommand';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'command has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new CommandsCommand()));
        $this->commandTester = new CommandTester($application->find('new:command'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->commandTester->execute(['new-command' => self::CLASS_NAME]);

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        require_once(self::URL_PATH . self::FILE_NAME);
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
    }
}
