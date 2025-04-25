<?php

declare(strict_types=1);

namespace Tests\Commands\Lion;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\InfoCommand;
use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCommandTest extends Test
{
    private CommandTester $commandTester;
    private InfoCommand $infoCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var InfoCommand $infoCommand */
        $infoCommand = new Container()->resolve(InfoCommand::class);

        $this->infoCommand = $infoCommand;

        $application = new Application();

        $application->add($this->infoCommand);

        $this->commandTester = new CommandTester($application->find('info'));

        $this->initReflection($this->infoCommand);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setComposerFactory(): void
    {
        $this->assertInstanceOf(InfoCommand::class, $this->infoCommand->setComposerFactory(new ComposerFactory()));
        $this->assertInstanceOf(ComposerFactory::class, $this->getPrivateProperty('composerFactory'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }
}
