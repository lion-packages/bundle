<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Route\Helpers\Rules;
use Lion\Route\Interface\RulesInterface;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RulesDBCommandTest extends Test
{
    private const string ENTITY = 'users';
    private const string IDRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule';
    private const string NAMERULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule';
    private const string LASTNAMERULE_NAMESPACE_REQUIRED =
        'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRequiredRule';
    private const string LASTNAMERULE_NAMESPACE_OPTIONAL =
        'App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameOptionalRule';
    private const string EMAILRULE_NAMESPACE = 'App\\Rules\\LionDatabase\\MySQL\\Users\\EmailRule';
    private const string OUTPUT_MESSAGE_ERROR =
        "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'lion_database.users' doesn't exist";
    private const string OUTPUT_MESSAGE_FOREIGN = "the rule for 'idroles' property has been omitted, it is a foreign";

    private CommandTester $commandTester;
    private RulesDBCommand $rulesDBCommand;

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var RulesCommand $rulesCommand */
        $rulesCommand = $container->resolve(RulesCommand::class);

        /** @var RulesDBCommand $rulesDBCommand */
        $rulesDBCommand = $container->resolve(RulesDBCommand::class);

        $this->rulesDBCommand = $rulesDBCommand;

        $application = new Application();

        $application->add($rulesCommand);

        $application->add($this->rulesDBCommand);

        $this->commandTester = new CommandTester($application->find('db:rules'));

        $this->initReflection($this->rulesDBCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        Schema::dropTable(self::ENTITY)
            ->execute();
    }

    private function createTables(): void
    {
        /** @var string $dbName */
        $dbName = env('DB_DEFAULT');

        Schema::connection($dbName)
            ->createTable('roles', function (): void {
                Schema::int('idroles')->notNull()->autoIncrement()->primaryKey();

                Schema::varchar('name', 255)->notNull()->comment('role description');
            })
            ->execute();

        Schema::connection($dbName)
            ->createTable(self::ENTITY, function (): void {
                Schema::int('id')->notNull()->autoIncrement()->primaryKey();

                Schema::int('idroles', 11)->notNull()->foreign('roles', 'idroles');

                Schema::varchar('name', 25)->notNull()->comment('username');

                Schema::varchar('last_name', 25)->null()->default('N/A');

                Schema::varchar('email', 150)->notNull()->comment('user email');
            })
            ->execute();
    }

    /**
     * @param array{
     *     field: string,
     *     desc: string,
     *     value: mixed,
     *     disabled: bool
     * } $options
     */
    private function assertColumn(string $display, string $namespace, array $options): void
    {
        $this->assertStringContainsString($namespace, $display);

        /** @var Rules $rules */
        $rules = new $namespace();

        $this->assertInstances($rules, [
            Rules::class,
            RulesInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $this->assertSame($options['field'], $rules->field);
        /** @phpstan-ignore-next-line */
        $this->assertSame($options['desc'], $rules->desc);
        /** @phpstan-ignore-next-line */
        $this->assertSame($options['value'], $rules->value);
        /** @phpstan-ignore-next-line */
        $this->assertSame($options['disabled'], $rules->disabled);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setFileWriter(): void
    {
        $this->assertInstanceOf(RulesDBCommand::class, $this->rulesDBCommand->setFileWriter(new FileWriter()));
        $this->assertInstanceOf(FileWriter::class, $this->getPrivateProperty('fileWriter'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(RulesDBCommand::class, $this->rulesDBCommand->setDatabaseEngine(new DatabaseEngine()));
        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->createTables();

        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

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

    #[Testing]
    public function executeWithoutColumns(): void
    {
        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::FAILURE, $execute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR, $this->commandTester->getDisplay());
    }

    #[Testing]
    public function executeWithForeign(): void
    {
        $this->createTables();

        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::SUCCESS, $execute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_FOREIGN, $this->commandTester->getDisplay());
    }
}
