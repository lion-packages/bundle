<?php

declare(strict_types=1);

namespace Tests\Commands\New;

use LionBundle\Commands\New\TestCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Kernel;
use LionFiles\Store;
use LionTest\Test;
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
        $this->commandTester->execute(['test' => self::CLASS_NAME]);

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
