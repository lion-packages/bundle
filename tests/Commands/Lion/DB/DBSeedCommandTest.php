<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class DBSeedCommandTest extends Test
{
    use ConnectionProviderTrait;

    const URL_PATH = './database/Seed/';
    const NAMESPACE_CLASS = 'Database\\Seed\\';
    const CLASS_NAME = 'TestSeed';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'run seed';
    const OUTPUT_MESSAGE_NEW_SEED = 'seed has been generated';
    const RUN = 1;

    private CommandTester $commandTester;
    private CommandTester $commandTesterNewSeed;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->createDirectory(self::URL_PATH);

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new SeedCommand()));

        $application->add((new Container())->injectDependencies(new DBSeedCommand()));

        $this->commandTester = new CommandTester($application->find('db:seed'));

        $this->commandTesterNewSeed = new CommandTester($application->find('new:seed'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNewSeed->execute(['seed' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NEW_SEED, $this->commandTesterNewSeed->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute(['--run' => 1]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
