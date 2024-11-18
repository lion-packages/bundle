<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class RulesCommandTest extends Test
{
    private const string URL_PATH = './app/Rules/';
    private const string NAMESPACE_CLASS = 'App\\Rules\\';
    private const string CLASS_NAME = 'TestRule';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'rule has been generated';
    private const string METHOD = 'passes';

    private CommandTester $commandTester;
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();

        $application = (new Kernel())->getApplication();

        $application->add($this->container->resolve(RulesCommand::class));

        $this->commandTester = new CommandTester($application->find('new:rule'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['rule' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = $this->container->resolve(self::OBJECT_NAME);

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertContains(self::METHOD, get_class_methods($objClass));
    }
}
