<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\RSACommand;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Security\RSA;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RSACommandTest extends Test
{
    private const string URL_PATH = 'keys/';
    private const string OUTPUT_MESSAGE = 'public and private';

    private CommandTester $commandTester;
    private RSACommand $rsaCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var RSACommand $rsaCommand */
        $rsaCommand = new Container()->resolve(RSACommand::class);

        $this->rsaCommand = $rsaCommand;

        $application = new Application();

        $application->addCommand($this->rsaCommand);

        $this->commandTester = new CommandTester($application->find('new:rsa'));

        $this->initReflection($this->rsaCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./storage/keys/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setRSA(): void
    {
        $this->assertInstanceOf(RSACommand::class, $this->rsaCommand->setRSA(new RSA()));
        $this->assertInstanceOf(RSA::class, $this->getPrivateProperty('rsa'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(RSACommand::class, $this->rsaCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['--path' => self::URL_PATH]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists('./storage/' . self::URL_PATH . 'public.key');
        $this->assertFileExists('./storage/' . self::URL_PATH . 'private.key');
    }
}
