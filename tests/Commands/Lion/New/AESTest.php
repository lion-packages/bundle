<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\AESCommand;
use Lion\Dependency\Injection\Container;
use Lion\Security\AES;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class AESTest extends Test
{
    private const string OUTPUT_MESSAGE = 'Keys created successfully';

    private CommandTester $commandTester;
    private AESCommand $aesCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var AESCommand $aesCommand */
        $aesCommand = new Container()->resolve(AESCommand::class);

        $this->aesCommand = $aesCommand;

        $application = new Application();

        $application->add($this->aesCommand);

        $this->commandTester = new CommandTester($application->find('new:aes'));

        $this->initReflection($this->aesCommand);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setAES(): void
    {
        $this->assertInstanceOf(AESCommand::class, $this->aesCommand->setAES(new AES()));
        $this->assertInstanceOf(AES::class, $this->getPrivateProperty('aes'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
