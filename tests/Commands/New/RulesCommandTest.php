<?php

declare(strict_types=1);

namespace Tests\Commands\New;

use LionBundle\Commands\New\RulesCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;

class RulesCommandTest extends Test
{
    const URL_PATH = './app/Rules/';
    const NAMESPACE_CLASS = 'App\\Rules\\';
    const CLASS_NAME = 'TestRule';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'rule has been generated';
    const METHOD = 'passes';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new RulesCommand()));
        $this->commandTester = new CommandTester($application->find('new:rule'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['rule' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        require_once(self::URL_PATH . self::FILE_NAME);
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertContains(self::METHOD, get_class_methods($objClass));
    }
}
