<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\TraitCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class TraitCommandTest extends Test
{
    const URL_PATH = './app/Traits/';
    const NAMESPACE_CLASS = 'App\\Traits\\';
    const CLASS_NAME = 'TestTrait';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'trait has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new TraitCommand()));
        $this->commandTester = new CommandTester($application->find('new:trait'));

        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['trait' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
