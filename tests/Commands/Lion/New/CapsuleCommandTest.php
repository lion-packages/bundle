<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
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
    private const array PROPERTIES = ['idusers:int', 'users_name:string', 'users_last_name'];

    private CommandTester $commandTester;
    private ClassFactory $classFactory;

    protected function setUp(): void
    {
        $this->classFactory = new ClassFactory();

        $application = new Application();

        $application->add((new Container())->injectDependencies(new CapsuleCommand()));

        $this->commandTester = new CommandTester($application->find('new:capsule'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

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

        /** @var CapsuleInterface $objClass */
        $objClass = new (self::OBJECT_NAME)();

        $this->initReflection($objClass);

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            CapsuleInterface::class,
        ]);

        $tableName = $objClass->getTableName();

        $this->assertIsString($tableName);
        $this->assertSame(self::CLASS_ENTITY, $tableName);

        $capsule = $objClass->capsule();

        $this->assertIsObject($capsule);

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

            $this->assertNull($this->getPrivateProperty($dataProperty->format->snake));

            $getter = $dataProperty->getter->name;
            $setter = $dataProperty->setter->name;

            $this->assertInstanceOf(self::OBJECT_NAME, $objClass->$setter(null));
            $this->assertNull($objClass->$getter());
        }
    }
}
