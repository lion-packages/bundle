<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\ShowDatabasesCommand;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Arr;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ShowDatabasesCommandTest extends Test
{
    private const string MYSQL = 'mysql';
    private const string PORT = '3306';
    private const string DATABASE_NAME = 'lion_database';
    private const string DATABASE_USER = 'root';

    private CommandTester $commandTester;
    private ShowDatabasesCommand $showDatabasesCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var ShowDatabasesCommand $showDatabasesCommand */
        $showDatabasesCommand = new Container()->resolve(ShowDatabasesCommand::class);

        $this->showDatabasesCommand = $showDatabasesCommand;

        $application = new Application();

        $application->add($this->showDatabasesCommand);

        $this->commandTester = new CommandTester($application->find('db:show'));

        $this->initReflection($this->showDatabasesCommand);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(ShowDatabasesCommand::class, $this->showDatabasesCommand->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(self::MYSQL, $display);
        $this->assertStringContainsString(self::PORT, $display);
        $this->assertStringContainsString(self::DATABASE_NAME, $display);
        $this->assertStringContainsString(self::DATABASE_USER, $display);
    }
}
