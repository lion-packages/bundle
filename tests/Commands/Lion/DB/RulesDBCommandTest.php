<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Route\Helpers\Rules;
use Lion\Route\Interface\RulesInterface;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RulesDBCommandTest extends Test
{
    private const string ENTITY = 'users';
    private const string IDRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule';
    private const string NAMERULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule';
    private const string LASTNAMERULE_NAMESPACE_REQUIRED = 'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRequiredRule';
    private const string LASTNAMERULE_NAMESPACE_OPTIONAL = 'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameOptionalRule';
    private const string EMAILRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\EmailRule';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->createTables();

        $application = new Application();

        $container = new Container();

        $application->add($container->resolve(RulesCommand::class));

        $application->add($container->resolve(RulesDBCommand::class));

        $this->commandTester = new CommandTester($application->find('db:rules'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        Schema::dropTable(self::ENTITY)->execute();
    }

    private function createTables(): void
    {
        Schema::connection(env('DB_NAME'))
            ->createTable(self::ENTITY, function (): void {
                Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('name', 25)->notNull()->comment('username');
                Schema::varchar('last_name', 25)->null()->default('N/A');
                Schema::varchar('email', 150)->notNull()->comment('user email');
            })
            ->execute();
    }

    private function assertColumn(string $display, string $namespace, array $options): void
    {
        $this->assertStringContainsString($namespace, $display);

        $rules = new $namespace();

        $this->assertInstances($rules, [
            Rules::class,
            RulesInterface::class,
        ]);

        $this->assertSame($options['field'], $rules->field);
        $this->assertSame($options['desc'], $rules->desc);
        $this->assertSame($options['value'], $rules->value);
        $this->assertSame($options['disabled'], $rules->disabled);
    }

    #[Testing]
    public function execute(): void
    {
        $execute = $this->commandTester->setInputs(['0'])->execute(['entity' => self::ENTITY]);

        $this->assertSame(Command::SUCCESS, $execute);

        $display = $this->commandTester->getDisplay();

        $this->assertColumn($display, self::IDRULE_NAMESPACE, [
            'field' => 'id',
            'desc' => '',
            'value' => '',
            'disabled' => false
        ]);

        $this->assertColumn($display, self::NAMERULE_NAMESPACE, [
            'field' => 'name',
            'desc' => 'username',
            'value' => '',
            'disabled' => false
        ]);

        $this->assertColumn($display, self::LASTNAMERULE_NAMESPACE_OPTIONAL, [
            'field' => 'last_name',
            'desc' => '',
            'value' => 'N/A',
            'disabled' => true
        ]);

        $this->assertColumn($display, self::LASTNAMERULE_NAMESPACE_REQUIRED, [
            'field' => 'last_name',
            'desc' => '',
            'value' => 'N/A',
            'disabled' => false
        ]);

        $this->assertColumn($display, self::EMAILRULE_NAMESPACE, [
            'field' => 'email',
            'desc' => 'user email',
            'value' => '',
            'disabled' => false
        ]);

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }
}
