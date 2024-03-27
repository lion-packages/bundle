<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\New\FactoryCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class FactoryCommandTest extends Test
{
    const URL_PATH = './database/Factory/';
    const NAMESPACE_CLASS = 'Database\\Factory\\';
    const CLASS_NAME = 'TestFactory';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'factory has been generated';
    const METHOD = ['columns', 'definition'];

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new FactoryCommand()));
        $this->commandTester = new CommandTester($application->find('new:factory'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['factory' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::METHOD, get_class_methods($objClass));

        $definition = $objClass->definition();

        $this->assertIsArray($definition);
        $this->assertCount(1, $definition);
    }
}
