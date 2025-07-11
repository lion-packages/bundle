<?php

declare(strict_types=1);

namespace Tests\Test;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Test\Test;
use Lion\Dependency\Injection\Container;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\Test\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    private Container $container;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->container = new Container();

        /** @var InterfaceCommand $interfaceCommand */
        $interfaceCommand = $this->container->resolve(InterfaceCommand::class);

        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = $this->container->resolve(CapsuleCommand::class);

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
}
