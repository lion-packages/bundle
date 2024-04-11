<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Helpers\Rules;
use Lion\Bundle\Interface\RulesInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class RulesDBCommandTest extends Test
{
    use ConnectionProviderTrait;

    const ENTITY = 'users';
    const IDRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule';
    const NAMERULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule';
    // const LASTNAMERULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRule';
    const LASTNAMERULE_NAMESPACE_REQUIRED = 'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRequiredRule';
    const LASTNAMERULE_NAMESPACE_OPTIONAL = 'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameOptionalRule';
    const EMAILRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\EmailRule';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        Schema::createTable(self::ENTITY, function () {
            Schema::int('id')->notNull()->autoIncrement()->primaryKey();
            Schema::varchar('name', 25)->notNull()->comment('username');
            Schema::varchar('last_name', 25)->null()->default('N/A');
            Schema::varchar('email', 150)->notNull()->comment('user email');
        })->execute();

        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new RulesCommand()));
        $application->add((new Container())->injectDependencies(new RulesDBCommand()));

        $this->commandTester = new CommandTester($application->find('db:rules'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        Schema::dropTable(self::ENTITY)->execute();
    }

    private function assertColumn(string $display, string $namespace, array $options): void
    {
        $rules = new $namespace();

        $this->assertInstances($rules, [Rules::class, RulesInterface::class]);
        $this->assertStringContainsString($namespace, $display);
        $this->assertSame($options['field'], $rules->field);
        $this->assertSame($options['desc'], $rules->desc);
        $this->assertSame($options['value'], $rules->value);
        $this->assertSame($options['disabled'], $rules->disabled);
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['entity' => self::ENTITY]));

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
    }
}
