<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Carbon\Carbon;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\Route\PostmanCollectionCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class PostmanCollectionCommandTest extends Test
{
    private const string URL_PATH = './storage/postman/';
    private const string CLASS_NAME_RULE = 'UsersNameRule';

    private CommandTester $commandTester;
    private CommandTester $commandTesterRule;

    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $application = (new Kernel)->getApplication();

        $container = new Container();

        $application->add($container->resolve(RulesCommand::class));

        $application->add($container->resolve(PostmanCollectionCommand::class));

        $this->commandTester = new CommandTester($application->find('route:postman'));

        $this->commandTesterRule = new CommandTester($application->find('new:rule'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);

        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterRule->execute(['rule' => self::CLASS_NAME_RULE]));

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $jsonFile = self::URL_PATH . Carbon::now()->format('Y_m_d') . '_lion_collection.json';

        $this->assertFileExists($jsonFile);

        $this->assertJsonFileEqualsJsonFile('./tests/Providers/Helpers/Commands/PostmanProvider.json', $jsonFile);
    }
}
