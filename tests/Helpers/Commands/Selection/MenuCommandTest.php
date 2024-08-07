<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Selection;

use Exception;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;
use Tests\Providers\Helpers\MenuCommandProviderTrait;

class MenuCommandTest extends Test
{
    use ConnectionProviderTrait;
    use MenuCommandProviderTrait;

    const VITE_PATH = './vite/';
    const PROJECT_PATH = 'example-project/';
    const PROJECT_PATH_SECOND = 'example-project-second/';
    const PROJECT_NAME = 'example-project';
    const PROJECT_NAME_SECOND = 'example-project-second';

    private MenuCommand $menuCommand;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->menuCommand = (new Container())
            ->injectDependencies(new MenuCommand());

        $this->initReflection($this->menuCommand);
    }

    protected function tearDown(): void
    {
        unset($_ENV['SELECTED_CONNECTION']);

        $this->rmdirRecursively(self::VITE_PATH);
    }

    public function testSelectedProjectNotAvailable(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('there are no projects available');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->createDirectory(self::VITE_PATH);

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

        $application = (new Kernel())
            ->getApplication();

        $application->add((new Container())
            ->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $commandTester->execute([]);
    }

    public function testSelectedProjectWithSingleProject(): void
    {
        $this->createDirectory(self::VITE_PATH . self::PROJECT_PATH);

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

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->execute([]));
        $this->assertStringContainsString(('(' . self::PROJECT_NAME . ')'), $commandTester->getDisplay());
    }

    public function testSelectedProjectWithMultipleProjects(): void
    {
        $this->createDirectory(self::VITE_PATH . self::PROJECT_PATH);

        $this->createDirectory(self::VITE_PATH . self::PROJECT_PATH_SECOND);

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

        $application = (new Kernel())
            ->getApplication();

        $application->add((new Container())
            ->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString(('(' . self::PROJECT_NAME_SECOND . ')'), $commandTester->getDisplay());
    }

    #[DataProvider('selectedTemplateProvider')]
    public function testSelectedTemplate(string $output, array $inputs): void
    {
        $command = new class extends MenuCommand
        {
            const VITE_TEMPLATES = ['Vanilla', 'Vue', 'React', 'Preact', 'Lit', 'Svelte', 'Solid', 'Qwik', 'Electron'];

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

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs($inputs)->execute([]));
        $this->assertStringContainsString($output, $commandTester->getDisplay());
    }

    public function testSelectedTypes(): void
    {
        $command = new class extends MenuCommand
        {
            const TYPES = ['js', 'ts'];

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

        $application = (new Kernel())
            ->getApplication();

        $application->add((new Container())
            ->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(js)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["1"])->execute([]));
        $this->assertStringContainsString("(ts)", $commandTester->getDisplay());
    }

    public function testSelectConnection(): void
    {
        $command = new class extends MenuCommand
        {
            const TYPES = ['js', 'ts'];

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

        $application = (new Kernel())
            ->getApplication();

        $application->add((new Container())
            ->injectDependencies($command));

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

    public function testSelectConnectionDefault(): void
    {
        $command = new class extends MenuCommand
        {
            const TYPES = ['js', 'ts'];

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

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database_test', $connections);

        $backupConnection = $connections['lion_database_test'];

        DB::removeConnection('lion_database_test');

        $connections = DB::getConnections();

        $this->assertArrayNotHasKey('lion_database_test', $connections);

        $application = (new Kernel())
            ->getApplication();

        $application->add((new Container())
            ->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs([""])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);

        DB::addConnection('lion_database_test', $backupConnection);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertArrayHasKey('lion_database_test', $connections);
    }

    public function testSelectConnectionByEnviromentEmpty(): void
    {
        $command = new class extends MenuCommand
        {
            const TYPES = ['js', 'ts'];

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

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(lion_database)", $commandTester->getDisplay());
        $this->assertSame($_ENV['SELECTED_CONNECTION'], 'lion_database');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    public function testSelectConnectionByEnviromentNotEmpty(): void
    {
        $command = new class extends MenuCommand
        {
            const TYPES = ['js', 'ts'];

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

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies($command));

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

    public function testSelectMigrationType(): void
    {
        $command = new class extends MenuCommand
        {
            const TABLE = 'Table';
            const VIEW = 'View';
            const STORE_PROCEDURE = 'Store-Procedure';
            const OPTIONS = [self::TABLE, self::VIEW, self::STORE_PROCEDURE];

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

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies($command));

        $commandTester = new CommandTester($application->find('test:menu:command'));

        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["0"])->execute([]));
        $this->assertStringContainsString("(Table)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["1"])->execute([]));
        $this->assertStringContainsString("(View)", $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->setInputs(["2"])->execute([]));
        $this->assertStringContainsString("(Store-Procedure)", $commandTester->getDisplay());
    }
}
