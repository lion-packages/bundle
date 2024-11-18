<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ClassCommandFactoryTest extends Test
{
    private ClassCommandFactory $classCommandFactory;

    protected function setUp(): void
    {
        $this->classCommandFactory = (new Container())->resolve(ClassCommandFactory::class);
    }

    #[Testing]
    public function execute(): void
    {
        $return = $this->classCommandFactory->execute(function () {
            return Command::SUCCESS;
        });

        $this->assertIsInt($return);
        $this->assertSame(Command::SUCCESS, $return);
    }

    #[Testing]
    public function getFactories(): void
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

    #[Testing]
    public function setFactories(): void
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

    #[Testing]
    public function getFactory(): void
    {
        $code = uniqid('code-');

        $this->assertInstanceOf(ClassCommandFactory::class, $this->classCommandFactory->setFactories([$code]));

        $factory = $this->classCommandFactory->getFactory($code);

        $this->assertIsObject($factory);
        $this->assertInstanceOf(ClassFactory::class, $factory);
    }

    #[Testing]
    public function getData(): void
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
