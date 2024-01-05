<?php

declare(strict_types=1);

namespace Tests\Commands\DB\Seed;

use LionBundle\Commands\DB\Seed\NewSeedCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NewSeedCommandTest extends Test
{
    const URL_PATH = './database/Seed/';
    const NAMESPACE_CLASS = 'Database\\Seed\\';
    const CLASS_NAME = 'TestSeed';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'seed has been generated';
    const METHOD = ['run'];

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NewSeedCommand()));
        $this->commandTester = new CommandTester($application->find('db:seed:new'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['seed' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        require_once(self::URL_PATH . self::FILE_NAME);
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::METHOD, get_class_methods($objClass));
    }
}
