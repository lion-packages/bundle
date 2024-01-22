<?php

declare(strict_types=1);

namespace Tests\Commands\DB;

use Lion\Bundle\Commands\DB\RulesDBCommand;
use Lion\Bundle\Commands\New\RulesCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class RulesDBCommandTest extends Test
{
    use ConnectionProviderTrait;

    const ENTITY = 'users';
    const RULES = [
        'App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule' => 'app/Rules/LionDatabase/MySQL/Users/IdRule.php',
        'App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule' => 'app/Rules/LionDatabase/MySQL/Users/NameRule.php',
        'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRule' => 'app/Rules/LionDatabase/MySQL/Users/LastNameRule.php'
    ];

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        Schema::createTable(self::ENTITY, function() {
             Schema::int('id')->notNull()->autoIncrement()->primaryKey()
                ->varchar('name', 25)->notNull()
                ->varchar('last_name', 25)->notNull();
        })->execute();

        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new RulesCommand()));
        $application->add((new Container())->injectDependencies(new RulesDBCommand()));
        $this->commandTester = new CommandTester($application->find('db:rules'));
    }

    protected function tearDown(): void
    {
        Schema::dropTable(self::ENTITY)->execute();
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['entity' => self::ENTITY]));

        $display = $this->commandTester->getDisplay();

        foreach (self::RULES as $namespace => $path) {
            $this->assertFileExists($path);
            $this->assertStringContainsString($namespace, $display);
        }
    }
}
