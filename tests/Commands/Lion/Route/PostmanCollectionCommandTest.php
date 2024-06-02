<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Carbon\Carbon;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\Route\PostmanCollectionCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\EnviromentProviderTrait;

class PostmanCollectionCommandTest extends Test
{
    use EnviromentProviderTrait;

    const URL_PATH = './storage/postman/';
    const OUTPUT_MESSAGE = 'Exported in';
    const NAMESPACE_RULE = 'App\\Rules\\UsersNameRule';
    const CLASS_NAME_RULE = 'UsersNameRule';

    private CommandTester $commandTester;
    private CommandTester $commandTesterRule;
    private Store $store;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $this->createDirectory(self::URL_PATH);

        $this->store = new Store();

        $application = (new Kernel)->getApplication();

        $application->add((new Container)->injectDependencies(new RulesCommand()));

        $application->add((new Container)->injectDependencies(new PostmanCollectionCommand()));

        $this->commandTester = new CommandTester($application->find('route:postman'));

        $this->commandTesterRule = new CommandTester($application->find('new:rule'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);

        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterRule->execute(['rule' => self::CLASS_NAME_RULE]));

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $jsonFile = self::URL_PATH . Carbon::now()->format('Y_m_d') . '_lion_collection.json';

        $this->assertFileExists($jsonFile);

        $this->assertJsonFileEqualsJsonFile('./tests/Providers/Helpers/Commands/PostmanProvider.json', $jsonFile);
    }
}
