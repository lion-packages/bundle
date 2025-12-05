<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Sockets;

use Lion\Bundle\Commands\Lion\New\SocketCommand;
use Lion\Bundle\Commands\Lion\Sockets\ServerSocketCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ServerSocketCommandTest extends Test
{
    private const string URL_PATH = './app/Sockets/';
    private const string NAMESPACE_CLASS = 'App\\Sockets\\';
    private const string CLASS_NAME = 'TestSockets';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'socket has been generated';
    private const string DEFINED_OUTPUT_MESSAGE = 'no sockets defined';
    private const string AVAILABLE_OUTPUT_MESSAGE = 'No sockets available';

    private CommandTester $commandTester;
    private CommandTester $commandTesterServer;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $container = new Container();

        $application->addCommand($container->resolve(SocketCommand::class));

        $application->addCommand($container->resolve(ServerSocketCommand::class));

        $this->commandTester = new CommandTester($application->find('new:socket'));

        $this->commandTesterServer = new CommandTester($application->find('socket:serve'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['socket' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @var MessageComponentInterface $socketObject */
        $socketObject = new (self::OBJECT_NAME)();

        $this->assertInstances($socketObject, [self::OBJECT_NAME, MessageComponentInterface::class]);
    }

    #[Testing]
    public function executeNoSocketsFolder(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTesterServer->execute([]));
        $this->assertStringContainsString(self::DEFINED_OUTPUT_MESSAGE, $this->commandTesterServer->getDisplay());
    }

    #[Testing]
    public function executeNoSocketsDefined(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->assertSame(Command::SUCCESS, $this->commandTesterServer->execute([]));

        $this->assertStringContainsString(self::AVAILABLE_OUTPUT_MESSAGE, $this->commandTesterServer->getDisplay());
    }
}
