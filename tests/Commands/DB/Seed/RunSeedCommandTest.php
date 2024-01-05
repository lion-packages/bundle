<?php

declare(strict_types=1);

namespace Tests\Commands\DB\Seed;

use LionBundle\Commands\DB\Seed\NewSeedCommand;
use LionBundle\Commands\DB\Seed\RunSeedCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\ConnectionTrait;

class RunSeedCommandTest extends Test
{
    use ConnectionTrait;

    const URL_PATH = './database/Seed/';
    const NAMESPACE_CLASS = 'Database\\Seed\\';
    const CLASS_NAME = 'TestSeed';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'SEED: SQLSTATE[42000]';
    const OUTPUT_MESSAGE_NEW_SEED = 'seed has been generated';
    const RUN = 1;

    private CommandTester $commandTester;
    private CommandTester $commandTesterNewSeed;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NewSeedCommand()));
        $application->add((new Container())->injectDependencies(new RunSeedCommand()));

        $this->commandTester = new CommandTester($application->find('db:seed:run'));
        $this->commandTesterNewSeed = new CommandTester($application->find('db:seed:new'));

        $this->runDatabaseConnections();
        $this->createDirectory(self::URL_PATH);
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNewSeed->execute(['seed' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NEW_SEED, $this->commandTesterNewSeed->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $this->commandTester->setInputs(['0']);
        $this->commandTester->execute(['--run' => 1]);

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
