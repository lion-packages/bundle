<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class DBSeedCommandTest extends Test
{
    use ConnectionProviderTrait;

    private const string URL_PATH = './database/Seed/';
    private const string NAMESPACE_CLASS = 'Database\\Seed\\';
    private const string CLASS_NAME = 'TestSeed';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'run seed';
    private const string OUTPUT_MESSAGE_NEW_SEED = 'seed has been generated';
    private const int RUN = 1;

    private CommandTester $commandTester;
    private CommandTester $commandTesterNewSeed;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->createDirectory(self::URL_PATH);

        $application = (new Kernel())->getApplication();

        $container = new Container();

        $application->add($container->resolve(SeedCommand::class));

        $application->add($container->resolve(DBSeedCommand::class));

        $this->commandTester = new CommandTester($application->find('db:seed'));

        $this->commandTesterNewSeed = new CommandTester($application->find('new:seed'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNewSeed->execute(['seed' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NEW_SEED, $this->commandTesterNewSeed->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute(['--run' => 1]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
