<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
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
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();

        $application = (new Kernel())->getApplication();
        $application->add($this->container->injectDependencies(new RulesCommand()));
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

        $objClass = $this->container->injectDependencies(new (self::OBJECT_NAME)());

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertContains(self::METHOD, get_class_methods($objClass));

        $objClass->passes();
    }
}
