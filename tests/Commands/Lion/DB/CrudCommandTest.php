<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\CrudCommand;
use Lion\Bundle\Commands\Lion\DB\MySQL\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\ControllerCommand;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class CrudCommandTest extends Test
{
    use ConnectionProviderTrait;

    const ENTITY = 'users';
    const CLASS_LIST = [
        'App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule' => 'app/Rules/LionDatabase/MySQL/Users/IdRule.php',
        'App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule' => 'app/Rules/LionDatabase/MySQL/Users/NameRule.php',
        'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRule' => 'app/Rules/LionDatabase/MySQL/Users/LastNameRule.php',
        'App\\Http\\Controllers\\LionDatabase\\MySQL\\UsersController' => 'app/Http/Controllers/LionDatabase/MySQL/UsersController.php',
        'App\\Models\\LionDatabase\\MySQL\\UsersModel' => 'app/Models/LionDatabase/MySQL/UsersModel.php',
        'Tests\\Models\\LionDatabase\\MySQL\\UsersModelTest' => 'tests/Models/LionDatabase/MySQL/UsersModelTest.php',
        'Tests\\Controllers\\LionDatabase\\MySQL\\UsersControllerTest' => 'tests/Controllers/LionDatabase/MySQL/UsersControllerTest.php',
        'Database\\Class\\LionDatabase\\MySQL\\Users' => 'database/Class/LionDatabase/MySQL/Users.php',
        'Tests\\Class\\LionDatabase\\MySQL\\UsersTest' => 'tests/Class/LionDatabase/MySQL/UsersTest.php',
    ];

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $this->runDatabaseConnections();
        $this->createTable();

        $kernel = new Kernel();
        $container = new Container();

        $kernel->commandsOnObjects([
            $container->injectDependencies(new TestCommand()),
            $container->injectDependencies(new ModelCommand()),
            $container->injectDependencies(new ControllerCommand()),
            $container->injectDependencies(new RulesCommand()),
            $container->injectDependencies(new CapsuleCommand()),
            $container->injectDependencies(new RulesDBCommand()),
            $container->injectDependencies(new DBCapsuleCommand()),
            $container->injectDependencies(new CrudCommand())
        ]);

        $this->commandTester = new CommandTester($kernel->getApplication()->find('db:crud'));
	}

	protected function tearDown(): void 
	{
        $this->rmdirRecursively('./app/');
        $this->rmdirRecursively('./database/');
        $this->rmdirRecursively('./tests/Class/');
        $this->rmdirRecursively('./tests/Controllers/');
        $this->rmdirRecursively('./tests/Models/');
        Schema::dropTable(self::ENTITY)->execute();
	}

    private function createTable(): void
    {
        Schema::createTable(self::ENTITY, function() {
             Schema::int('id')->notNull()->autoIncrement()->primaryKey()
                ->varchar('name', 25)->notNull()
                ->varchar('last_name', 25)->notNull();
        })->execute();
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['entity' => self::ENTITY]));

        $display = $this->commandTester->getDisplay();

        foreach (self::CLASS_LIST as $namespace => $file) {
            $this->assertFileExists($file);
            $this->assertStringContainsString($namespace, $display);
        }
    }
}
