<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;

class ClassCommandFactoryTest extends Test
{
    private ClassCommandFactory $classCommandFactory;

    protected function setUp(): void
    {
        $this->classCommandFactory = (new Container())
            ->injectDependencies(new ClassCommandFactory());
    }

    public function testExecute(): void
    {
        $return = $this->classCommandFactory->execute(function () {
            return Command::SUCCESS;
        });

        $this->assertIsInt($return);
        $this->assertSame(Command::SUCCESS, $return);
    }

    public function testGetFactories(): void
    {
        $code = uniqid('code-');

        $this->classCommandFactory->setFactories([$code]);

        $factories = $this->classCommandFactory->getFactories();

        $this->assertIsArray($factories);
        $this->assertArrayHasKey($code, $factories);

        $row = $factories[$code];

        $this->assertIsObject($row);
        $this->assertInstanceOf(ClassFactory::class, $row);
    }

    public function testSetFactories(): void
    {
        $code = uniqid('code-');

        $this->assertInstanceOf(ClassCommandFactory::class, $this->classCommandFactory->setFactories([$code]));

        $factories = $this->classCommandFactory->getFactories();

        $this->assertIsArray($factories);
        $this->assertArrayHasKey($code, $factories);

        $row = $factories[$code];

        $this->assertIsObject($row);
        $this->assertInstanceOf(ClassFactory::class, $row);
    }

    public function testGetFactory(): void
    {
        $code = uniqid('code-');

        $this->assertInstanceOf(ClassCommandFactory::class, $this->classCommandFactory->setFactories([$code]));

        $factory = $this->classCommandFactory->getFactory($code);

        $this->assertIsObject($factory);
        $this->assertInstanceOf(ClassFactory::class, $factory);
    }

    public function testGetData(): void
    {
        $code = uniqid('code-');

        $this->assertInstanceOf(ClassCommandFactory::class, $this->classCommandFactory->setFactories([$code]));

        $factory = $this->classCommandFactory->getFactory($code);

        $this->assertIsObject($factory);
        $this->assertInstanceOf(ClassFactory::class, $factory);

        $data = $this->classCommandFactory->getData($factory, [
            'path' => 'app/Http/Services/',
            'class' => 'TestService',
        ]);

        $this->assertIsObject($data);
        $this->assertObjectHasProperty('folder', $data);
        $this->assertObjectHasProperty('class', $data);
        $this->assertObjectHasProperty('namespace', $data);
        $this->assertSame('app/Http/Services/', $data->folder);
        $this->assertSame('TestService', $data->class);
        $this->assertSame('App\\Http\\Services', $data->namespace);
    }
}
