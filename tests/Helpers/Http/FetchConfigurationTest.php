<?php

declare(strict_types=1);

namespace Tests\Helpers\Http;

use Lion\Bundle\Helpers\Http\FetchConfiguration;
use Lion\Bundle\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class FetchConfigurationTest extends Test
{
    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function constructor(): void
    {
        $configuration = [
            'verify' => true,
        ];

        $fetchConfiguration = new FetchConfiguration($configuration);

        $this->initReflection($fetchConfiguration);

        $this->assertSame($configuration, $this->getPrivateProperty('configuration'));
    }

    #[Testing]
    public function getConfiguration(): void
    {
        $configuration = [
            'verify' => true,
        ];

        $fetchConfiguration = new FetchConfiguration($configuration);

        $this->assertSame($configuration, $fetchConfiguration->getConfiguration());
    }
}
