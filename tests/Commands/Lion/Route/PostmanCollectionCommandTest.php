<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use Carbon\Carbon;
use Lion\Bundle\Commands\Lion\Route\PostmanCollectionCommand;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\EnviromentProviderTrait;

class PostmanCollectionCommandTest extends Test
{
    use EnviromentProviderTrait;

    const URL_PATH = './storage/postman/';
    const OUTPUT_MESSAGE = 'Exported in';

    private CommandTester $commandTester;
    private Store $store;

    protected function setUp(): void
    {
        $this->loadEnviroment();
        $this->createDirectory(self::URL_PATH);

        $this->store = new Store();
        $application = (new Kernel)->getApplication();

        $application->add((new Container)->injectDependencies(new PostmanCollectionCommand()));
        $this->commandTester = new CommandTester($application->find('route:postman'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    public function testExecute(): void
    {
        $file = self::URL_PATH . Carbon::now()->format('Y_m_d') . '_lion_collection.json';

        Routes::setRules(['POST' => []]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertFileExists($file);

        $jsonCollection = $this->store->get(self::URL_PATH . Carbon::now()->format('Y_m_d') . '_lion_collection.json');
        $jsonProvider = $this->store->get('./tests/Providers/PostmanProvider.json');

        $this->assertSame($jsonProvider, $jsonCollection);
    }
}
