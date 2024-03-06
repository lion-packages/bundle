<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Sockets;

use Lion\Bundle\Commands\Lion\New\SocketCommand;
use Lion\Bundle\Commands\Lion\Sockets\ServerSocketCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ServerSocketCommandTest extends Test
{
    const URL_PATH = './app/Http/Sockets/';
    const NAMESPACE_CLASS = 'App\\Http\\Sockets\\';
    const CLASS_NAME = 'TestSockets';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'socket has been generated';
    const DEFINED_OUTPUT_MESSAGE = 'no sockets defined';
    const AVAILABLE_OUTPUT_MESSAGE = 'No sockets available';

    private CommandTester $commandTester;
    private CommandTester $commandTesterServer;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new SocketCommand()));
        $application->add((new Container())->injectDependencies(new ServerSocketCommand()));

        $this->commandTester = new CommandTester($application->find('new:socket'));
        $this->commandTesterServer = new CommandTester($application->find('socket:serve'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecuteNoSocketsFolder(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTesterServer->execute([]));
        $this->assertStringContainsString(self::DEFINED_OUTPUT_MESSAGE, $this->commandTesterServer->getDisplay());
    }

    public function testExecuteNoSocketsDefined(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->assertSame(Command::SUCCESS, $this->commandTesterServer->execute([]));
        $this->assertStringContainsString(self::AVAILABLE_OUTPUT_MESSAGE, $this->commandTesterServer->getDisplay());
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['socket' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @var MessageComponentInterface $socketObject */
        $socketObject = new (self::OBJECT_NAME)();

        $this->assertInstances($socketObject, [self::OBJECT_NAME, MessageComponentInterface::class]);
        // $this->assertWithOb(fn() => $this->assertSame(Command::SUCCESS, $this->commandTesterServer->execute([])));
    }
}
