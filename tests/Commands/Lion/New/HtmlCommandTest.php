<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\HtmlCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\HtmlInterface;
use Lion\Bundle\Support\Html;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class HtmlCommandTest extends Test
{
    private const string URL_PATH = './app/Html/';
    private const string NAMESPACE_CLASS = 'App\\Html\\';
    private const string CLASS_NAME = 'TestHtml';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The html class has been generated successfully.';

    private CommandTester $commandTester;
    private HtmlCommand $htmlCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var HtmlCommand $htmlCommand */
        $htmlCommand = new Container()->resolve(HtmlCommand::class);

        $this->htmlCommand = $htmlCommand;

        $application = new Application();

        $application->add($this->htmlCommand);

        $this->commandTester = new CommandTester($application->find('new:html'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->htmlCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(HtmlCommand::class, $this->htmlCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(HtmlCommand::class, $this->htmlCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'html' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);

        /** @phpstan-ignore-next-line */
        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            Html::class,
            HtmlInterface::class
        ]);
    }
}
