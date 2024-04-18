<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\HtmlCommand;
use Lion\Bundle\Helpers\Commands\Html;
use Lion\Bundle\Interface\HtmlInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class HtmlCommandTest extends Test
{
    const URL_PATH = './app/Html/';
    const NAMESPACE_CLASS = 'App\\Html\\';
    const CLASS_NAME = 'TestHtml';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'html has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new HtmlCommand()));

        $this->commandTester = new CommandTester($application->find('new:html'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['html' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            Html::class,
            HtmlInterface::class
        ]);
    }
}
