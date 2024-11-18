<?php

declare(strict_types=1);

namespace Tests\Commands\Lion;

use Lion\Bundle\Commands\Lion\InfoCommand;
use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCommandTest extends Test
{
    private CommandTester $commandTester;
    private InfoCommand $infoCommand;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->infoCommand = (new InfoCommand())
            ->setArr(new Arr())
            ->setComposerFactory(
                (new ComposerFactory())
                    ->setArr(new Arr())
            );

        $application = new Application();

        $application->add($this->infoCommand);

        $this->commandTester = new CommandTester($application->find('info'));

        $this->initReflection($this->infoCommand);
    }

    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(InfoCommand::class, $this->infoCommand->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

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
