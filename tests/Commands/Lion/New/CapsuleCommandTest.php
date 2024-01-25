<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class CapsuleCommandTest extends Test
{
    const URL_PATH = './database/Class/';
    const NAMESPACE_CLASS = 'Database\\Class\\';
    const CLASS_NAME = 'TestCapsule';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'capsule has been generated';
    const PROPIERTIES = ['idusers:int', 'users_name:string', 'users_last_name'];

    private CommandTester $commandTester;
    private ClassFactory $classFactory;

	protected function setUp(): void
    {
        $this->classFactory = new ClassFactory();

        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new CapsuleCommand()));
        $this->commandTester = new CommandTester($application->find('new:capsule'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    public function testExecute(): void
    {
        $commandExecute = $this->commandTester->execute([
            'capsule' => self::CLASS_NAME,
            '--propierties' => self::PROPIERTIES
        ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        require_once(self::URL_PATH . self::FILE_NAME);
        $objClass = new (self::OBJECT_NAME)();
        $this->initReflection($objClass);

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);

        foreach (self::PROPIERTIES as $propierty) {
            $split = explode(':', $propierty);
            $type = !empty($split[1]) ? $split[1] : 'string';
            $dataPropierty = $this->classFactory->getPropierty($split[0], self::CLASS_NAME, $type);

            $this->assertNull($this->getPrivateProperty($dataPropierty->format));

            $getter = $dataPropierty->getter->name;
            $setter = $dataPropierty->setter->name;

            $this->assertInstanceOf(self::OBJECT_NAME, $objClass->$setter(null));
            $this->assertNull($objClass->$getter());
        }
    }
}
