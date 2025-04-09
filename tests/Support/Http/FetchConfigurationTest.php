<?php

declare(strict_types=1);

namespace Tests\Support\Http;

use Lion\Bundle\Support\Http\FetchConfiguration;
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

        $this->initReflection(new FetchConfiguration($configuration));

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
