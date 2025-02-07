<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Selection;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Database\Connection;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\Helpers\MenuCommandProviderTrait;

class MenuCommandTest extends Test
{
    use MenuCommandProviderTrait;

    private const string RESOURCES_PATH = 'resources/';
    private const string PROJECT_PATH = 'example-project/';
    private const string PROJECT_PATH_SECOND = 'example-project-second/';
    private const string PROJECT_NAME = 'example-project';
    private const string PROJECT_NAME_SECOND = 'example-project-second';

    private Container $container;
    private MenuCommand $menuCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $this->container = new Container();

        /** @var MenuCommand $menuCommand */
        $menuCommand = $this->container->resolve(MenuCommand::class);

        $this->menuCommand = $menuCommand;

        $this->initReflection($this->menuCommand);
    }

    protected function tearDown(): void
    {
        unset($_ENV['SELECTED_CONNECTION']);

        $this->rmdirRecursively(self::RESOURCES_PATH);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(MenuCommand::class, $this->menuCommand->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(MenuCommand::class, $this->menuCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function initialize(): void
    {
        $this->getPrivateMethod('initialize', [
            'input' => new StringInput('input'),
            'output' => new BufferedOutput(),
        ]);

        $this->assertInstanceOf(StringInput::class, $this->getPrivateProperty('input'));
        $this->assertInstanceOf(BufferedOutput::class, $this->getPrivateProperty('output'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(MenuCommand::class, $this->menuCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    #[Testing]
    public function selectedProjectNotAvailable(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('there are no projects available');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->createDirectory(self::RESOURCES_PATH);

        $command = new class extends MenuCommand
        {
            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $project = $this->selectedProject($input, $output);

                $output->write("({$project})");

                return Command::SUCCESS;
            }
        };

        /** @var MenuCommand $command */
        $command = $this->container->resolve($command::class);

        $application = new Application();

        $application->add($command);

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $commandTester->execute([]);
    }

    #[Testing]
    public function selectedProjectWithSingleProject(): void
    {
        $this->createDirectory(self::RESOURCES_PATH . self::PROJECT_PATH);

        $command = new class extends MenuCommand
        {
            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $project = $this->selectedProject($input, $output);

                $output->write("({$project})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->execute([]));
        $this->assertStringContainsString(('(' . self::PROJECT_NAME . ')'), $commandTester->getDisplay());
    }

    #[Testing]
    public function selectedProjectWithMultipleProjects(): void
    {
        $this->createDirectory(self::RESOURCES_PATH . self::PROJECT_PATH);

        $this->createDirectory(self::RESOURCES_PATH . self::PROJECT_PATH_SECOND);

        $command = new class extends MenuCommand
        {
            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $project = $this->selectedProject($input, $output);

                $output->write("({$project})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(('(' . self::PROJECT_NAME_SECOND . ')'), $commandTester->getDisplay());
    }

    #[Testing]
    #[DataProvider('selectedTemplateProvider')]
    public function selectedTemplate(string $output, array $inputs): void
    {
        $command = new class extends MenuCommand
        {
            private const array VITE_TEMPLATES = [
                'Vanilla',
                'Vue',
                'React',
                'Preact',
                'Lit',
                'Svelte',
                'Solid',
                'Qwik',
                'Electron',
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $template = $this->selectedTemplate($input, $output, self::VITE_TEMPLATES, 'React', 2);

                $output->write("({$template})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs($inputs)->execute([]));
        $this->assertStringContainsString($output, $commandTester->getDisplay());
    }

    #[Testing]
    public function selectedTypes(): void
    {
        $command = new class extends MenuCommand
        {
            private const array TYPES = [
                'js',
                'ts',
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $type = $this->selectedTypes($input, $output, self::TYPES);

                $output->write("({$type})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(js)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["1"])->execute([]));
        $this->assertStringContainsString("(ts)", $commandTester->getDisplay());
    }

    #[Testing]
    public function selectConnection(): void
    {
        $command = new class extends MenuCommand
        {
            private const array TYPES = [
                'js',
                'ts',
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $connection = $this->selectConnection($input, $output);

                $output->write("({$connection})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["1"])->execute([]));
        $this->assertStringContainsString("(lion_database_test)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database_test');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    #[Testing]
    public function selectConnectionDefault(): void
    {
        $command = new class extends MenuCommand
        {
            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $connection = $this->selectConnection($input, $output);

                $output->write("({$connection})");

                return Command::SUCCESS;
            }
        };

        /** @var MenuCommand $command */
        $command = $this->container->resolve($command::class);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database_test', $connections);
        $this->assertArrayHasKey('lion_database_postgres', $connections);

        $lionDatabaseTest = $connections['lion_database_test'];

        $lionDatabasePostgres = $connections['lion_database_postgres'];

        DB::removeConnection('lion_database_test');
        DB::removeConnection('lion_database_postgres');

        $connections = DB::getConnections();

        $this->assertArrayNotHasKey('lion_database_test', $connections);
        $this->assertArrayNotHasKey('lion_database_postgres', $connections);

        $application = new Application();

        $application->add($command);

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs([""])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);

        DB::addConnection('lion_database_test', $lionDatabaseTest);
        DB::addConnection('lion_database_postgres', $lionDatabasePostgres);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertArrayHasKey('lion_database_test', $connections);
    }

    #[Testing]
    public function selectConnectionByEnviromentEmpty(): void
    {
        $command = new class extends MenuCommand
        {
            private const array TYPES = [
                'js',
                'ts',
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $connection = $this->selectConnectionByEnviroment($input, $output);

                $output->write("({$connection})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    #[Testing]
    public function selectConnectionByEnviromentNotEmpty(): void
    {
        $command = new class extends MenuCommand
        {
            private const array TYPES = [
                'js',
                'ts',
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $connection = $this->selectConnectionByEnviroment($input, $output);

                $output->write("({$connection})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    #[Testing]
    public function selectMigrationType(): void
    {
        $command = new class extends MenuCommand
        {
            private const string TABLE = 'Table';
            private const string VIEW = 'View';
            private const string STORE_PROCEDURE = 'Store-Procedure';
            private const array OPTIONS = [
                self::TABLE,
                self::VIEW,
                self::STORE_PROCEDURE,
            ];

            protected function configure(): void
            {
                $this->setName('test:menu:command');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $connection = $this->selectMigrationType($input, $output, self::OPTIONS);

                $output->write("({$connection})");

                return Command::SUCCESS;
            }
        };

        $application = new Application();

        $application->add($this->container->resolve($command::class));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(Table)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["1"])->execute([]));
        $this->assertStringContainsString("(View)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["2"])->execute([]));
        $this->assertStringContainsString("(Store-Procedure)", $commandTester->getDisplay());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getTableColumnsIsEmpty(): void
    {
        $columns = $this->getPrivateMethod('getTableColumns', [
            'driver' => 'not-exists',
            'selectedConnection' => 'test',
            'entity' => 'test',
        ]);

        $this->assertIsArray($columns);
        $this->assertEmpty($columns);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getTableForeignsIsEmpty(): void
    {
        $foreigns = $this->getPrivateMethod('getTableForeigns', [
            'driver' => 'not-exists',
            'selectedConnection' => 'test',
            'entity' => 'test',
        ]);

        $this->assertIsArray($foreigns);
        $this->assertEmpty($foreigns);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getTablesConnectionNotExists(): void
    {
        $foreigns = $this->getPrivateMethod('getTables', [
            'connectionName' => 'err-connection',
        ]);

        $this->assertIsArray($foreigns);
        $this->assertEmpty($foreigns);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getTablesIsEmpty(): void
    {
        Connection::addConnection('err-connection', [
            'type' => 'not-exists',
            'host' => 'mysql',
            'port' => 3306,
            'dbname' => 'lion_database',
            'user' => 'root',
            'password' => 'lion',
        ]);

        $foreigns = $this->getPrivateMethod('getTables', [
            'connectionName' => 'err-connection',
        ]);

        $this->assertIsArray($foreigns);
        $this->assertEmpty($foreigns);

        Connection::removeConnection('err-connection');
    }
}
