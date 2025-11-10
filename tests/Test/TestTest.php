<?php

declare(strict_types=1);

namespace Tests\Test;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Test\Test;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\Test\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    private CommandTester $commandTester;

    /**
     * @throws DependencyException Error while resolving the entry
     * @throws NotFoundException No entry found for the given name
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var InterfaceCommand $interfaceCommand */
        $interfaceCommand = $container->resolve(InterfaceCommand::class);

        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = $container->resolve(CapsuleCommand::class);

        $application = new Application();

        $application->add($interfaceCommand);

        $application->add($capsuleCommand);

        $this->commandTester = new CommandTester($application->find('new:capsule'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');
    }

    /**
     * @param string $capsule
     * @param string $entity
     * @param array<int, string> $properties
     * @param array<int, string> $files
     * @param array<class-string, array{
     *     column: string,
     *     set: mixed,
     *     get: mixed
     * }> $interfaces
     *
     * @throws DependencyException Error while resolving the entry
     * @throws NotFoundException No entry found for the given name
     * @throws ReflectionException
     */
    #[Testing]
    #[RunInSeparateProcess]
    #[DataProvider('assertCapsuleProvider')]
    public function assertCapsuleTest(
        string $capsule,
        string $entity,
        array $properties,
        array $files,
        array $interfaces
    ): void {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'capsule' => $capsule,
            '--entity' => $entity,
            '--properties' => $properties,
        ]));

        foreach ($files as $file) {
            $this->assertFileExists($file);

            require_once $file;
        }

        $this->assertCapsule("Database\\Class\\{$capsule}", $entity, $interfaces);
    }

    #[Testing]
    #[RunInSeparateProcess]
    #[TestWith(['envName' => 'SERVER_URL', 'value' => 'http://sandbox.local1'], 'case-0')]
    #[TestWith(['envName' => 'SERVER_URL_AUD', 'value' => 'http://sandbox.local2'], 'case-1')]
    public function runInSeparateEnvironmentRestoresOriginal(string $envName, string $value): void
    {
        $original = env($envName);

        $this->assertFalse(Env::isSandboxActive());

        $this->runInSeparateEnvironment(function () use ($envName, $value): void {
            $this->assertTrue(Env::isSandboxActive());

            Env::set($envName, $value);

            $this->assertSame($value, env($envName));
        });

        $this->assertFalse(Env::isSandboxActive());
        $this->assertSame($original, env($envName));
    }

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    #[Testing]
    #[Group('parallel')]
    #[RunInSeparateProcess]
    #[TestWith(['connection' => 'local'])]
    #[TestWith(['connection' => 'lion_database_test'])]
    public function runInSeparateDatabaseTest(string $connection): void
    {
        $this->runInSeparateDatabase(function () use ($connection): void {
            Schema::connection($connection)
                ->createTable('users', function (): void {
                    Schema::int('idusers')
                        ->primaryKey()
                        ->autoIncrement()
                        ->notNull();

                    Schema::varchar('users_name', 25)
                        ->notNull();
                })
                ->execute();

            Schema::connection($connection)
                ->createView('read_users', function (MySQL $db): void {
                    $db
                        ->table('users')
                        ->select();
                })
                ->execute();

            Schema::connection($connection)
                ->createStoredProcedure('create_users', function (): void {
                    Schema::in()->varchar('_users_name', 25);
                }, function (MySQL $db): void {
                    $db
                        ->table('users')
                        ->insert([
                            'users_name' => '_users_name'
                        ]);
                })
                ->execute();

            MySQL::connection($connection)
                ->call('create_users', [
                    'root',
                ])
                ->execute();

            $rows = MySQL::connection($connection)
                ->view('read_users')
                ->select()
                ->getAll();

            $this->assertIsArray($rows);
            $this->assertNotEmpty($rows);

            $row = $rows[0];

            $this->assertIsObject($row);
            $this->assertInstanceOf(stdClass::class, $row);
            $this->assertObjectHasProperty('users_name', $row);
            $this->assertSame('root', $row->users_name);
        });
    }
}
