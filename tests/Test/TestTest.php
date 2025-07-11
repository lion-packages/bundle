<?php

declare(strict_types=1);

namespace Tests\Test;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Test\Test;
use Lion\Dependency\Injection\Container;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TestTest extends Test
{
    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');
    }

    /**
     * @param string $capsule
     * @param string $entity
     * @param array<int, string> $properties
     *
     * @throws DependencyException Error while resolving the entry
     * @throws NotFoundException No entry found for the given name
     * @throws ReflectionException
     */
    #[Testing]
    #[RunInSeparateProcess]
    #[TestWith(['capsule' => 'Users', 'entity' => 'users', 'properties' => ['id:int', 'name:string']], 'case-0')]
    #[TestWith(['capsule' => 'Roles', 'entity' => 'roles', 'properties' => ['id:int', 'name:string']], 'case-1')]
    public function assertCapsuleTest(string $capsule, string $entity, array $properties): void
    {
        $container = new Container();

        /** @var InterfaceCommand $interfaceCommand */
        $interfaceCommand = $container->resolve(InterfaceCommand::class);

        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = $container->resolve(CapsuleCommand::class);

        $application = new Application();

        $application->add($interfaceCommand);

        $application->add($capsuleCommand);

        $commandTester = new CommandTester($application->find('new:capsule'));

        $this->assertSame(Command::SUCCESS, $commandTester->execute([
            'capsule' => $capsule,
            '--entity' => $entity,
            '--properties' => $properties,
        ]));

        $this->assertFileExists("database/Class/{$capsule}.php");
        $this->assertFileExists("app/Interfaces/Database/Class/{$capsule}/IdInterface.php");
        $this->assertFileExists("app/Interfaces/Database/Class/{$capsule}/NameInterface.php");

        require_once "database/Class/{$capsule}.php";

        require_once "app/Interfaces/Database/Class/{$capsule}/IdInterface.php";

        require_once "app/Interfaces/Database/Class/{$capsule}/NameInterface.php";

        /** @phpstan-ignore-next-line */
        $this->assertCapsule("Database\\Class\\{$capsule}", $entity, [
            "App\\Interfaces\\Database\\Class\\{$capsule}\\IdInterface" => 1,
            "App\\Interfaces\\Database\\Class\\{$capsule}\\NameInterface" => 'test name',
        ]);
    }
}
