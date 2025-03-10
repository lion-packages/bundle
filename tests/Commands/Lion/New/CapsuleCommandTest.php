<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Helpers\Commands\Capsule\CapsuleFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use stdClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CapsuleCommandTest extends Test
{
    private const string URL_PATH = './database/Class/';
    private const string NAMESPACE_CLASS = 'Database\\Class\\';
    private const string CLASS_NAME = 'TestCapsule';
    private const string CLASS_ENTITY = 'test';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'capsule has been generated';
    private const array PROPERTIES = [
        'idusers:int',
        'users_name:string',
        'users_last_name',
    ];

    private CommandTester $commandTester;
    private CapsuleCommand $capsuleCommand;
    private ClassFactory $classFactory;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = new Container()->resolve(CapsuleCommand::class);

        $this->capsuleCommand = $capsuleCommand;

        $this->classFactory = new ClassFactory();

        $application = new Application();

        $application->add($this->capsuleCommand);

        $this->commandTester = new CommandTester($application->find('new:capsule'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->capsuleCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(CapsuleCommand::class, $this->capsuleCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(CapsuleCommand::class, $this->capsuleCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setCapsuleFactory(): void
    {
        $this->assertInstanceOf(CapsuleCommand::class, $this->capsuleCommand->setCapsuleFactory(new CapsuleFactory()));
        $this->assertInstanceOf(CapsuleFactory::class, $this->getPrivateProperty('capsuleFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function execute(): void
    {
        $commandExecute = $this->commandTester->execute([
            'capsule' => self::CLASS_NAME,
            '--entity' => self::CLASS_ENTITY,
            '--properties' => self::PROPERTIES,
        ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->initReflection($objClass);

        /** @phpstan-ignore-next-line */
        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            CapsuleInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $tableName = $objClass->getTableName();

        $this->assertIsString($tableName);
        $this->assertSame(self::CLASS_ENTITY, $tableName);

        /** @phpstan-ignore-next-line */
        $capsule = $objClass->capsule();

        $this->assertIsObject($capsule);

        /** @phpstan-ignore-next-line */
        $this->assertInstances($capsule, [
            self::OBJECT_NAME,
            CapsuleInterface::class,
        ]);

        foreach (self::PROPERTIES as $property) {
            $split = explode(':', $property);

            $dataProperty = $this->classFactory->getProperty(
                $split[0],
                self::CLASS_NAME,
                (!empty($split[1]) ? $split[1] : 'string'),
                ClassFactory::PRIVATE_PROPERTY
            );

            /** @var stdClass $format */
            $format = $dataProperty->format;

            /** @var string $snakeCase */
            $snakeCase = $format->snake;

            $this->assertNull($this->getPrivateProperty($snakeCase));

            /** @var stdClass $getter */
            $getter = $dataProperty->getter;

            /** @var stdClass $setter */
            $setter = $dataProperty->setter;

            /** @var string $getterName */
            $getterName = $getter->name;

            /** @var string $setterName */
            $setterName = $setter->name;

            /** @phpstan-ignore-next-line */
            $this->assertInstanceOf(self::OBJECT_NAME, $objClass->$setterName(null));
            $this->assertNull($objClass->$getterName());
        }
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function executeWithoutProperties(): void
    {
        $commandExecute = $this->commandTester->execute([
            'capsule' => self::CLASS_NAME,
            '--entity' => self::CLASS_ENTITY,
        ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->initReflection($objClass);

        $this->assertIsObject($objClass);

        /** @phpstan-ignore-next-line */
        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            CapsuleInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $tableName = $objClass->getTableName();

        $this->assertIsString($tableName);
        $this->assertSame(self::CLASS_ENTITY, $tableName);

        /** @phpstan-ignore-next-line */
        $capsule = $objClass->capsule();

        $this->assertIsObject($capsule);

        /** @phpstan-ignore-next-line */
        $this->assertInstances($capsule, [
            self::OBJECT_NAME,
            CapsuleInterface::class,
        ]);
    }
}
