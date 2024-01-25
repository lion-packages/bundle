<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class TestCommandTest extends Test
{
    const URL_PATH = './tests/';
    const NAMESPACE_CLASS = 'Tests\\';
    const CLASS_NAME = 'TestTest';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'test has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new TestCommand()));
        $this->commandTester = new CommandTester($application->find('new:test'));
	}

	protected function tearDown(): void 
	{
        (new Store())->remove('./tests/' . self::FILE_NAME);
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['test' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
