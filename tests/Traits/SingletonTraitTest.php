<?php

declare(strict_types=1);

namespace Tests\Traits;

use Lion\Bundle\Interface\SingletonInterface;
use Lion\Test\Test;
use Tests\Providers\Traits\SingletonProvider;

class SingletonTraitTest extends Test
{
    public function testGetInstance(): void
    {
        $instance = SingletonProvider::getInstance();

        $this->assertInstances($instance, [
            SingletonProvider::class,
            SingletonInterface::class,
        ]);
    }

    public function testGetInstanceWithMultipleInstances(): void
    {
        $instance = SingletonProvider::getInstance();

        $this->assertInstances($instance, [
            SingletonProvider::class,
            SingletonInterface::class,
        ]);

        $newInstance = SingletonProvider::getInstance();

        $this->assertInstances($newInstance, [
            SingletonProvider::class,
            SingletonInterface::class,
        ]);

        $this->assertSame($instance, $newInstance);
    }
}
