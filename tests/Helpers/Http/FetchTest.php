<?php

declare(strict_types=1);

namespace Tests\Helpers\Http;

use Lion\Bundle\Helpers\Http\Fetch;
use Lion\Bundle\Helpers\Http\FetchConfiguration;
use Lion\Bundle\Test\Test;
use Lion\Request\Http;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class FetchTest extends Test
{
    private const string HTTP_METHOD = Http::POST;
    private const string URI = 'https://locahost';
    private const array OPTIONS = [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ];
    private const array CONFIGURATION = [
        'verify' => false,
    ];

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function constructor(): void
    {
        $fetchConfiguration = new FetchConfiguration(self::CONFIGURATION);

        $this->initReflection(
            (new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS))
                ->setFetchConfiguration($fetchConfiguration)
        );

        $this->assertSame(self::HTTP_METHOD, $this->getPrivateProperty('httpMethod'));
        $this->assertSame(self::URI, $this->getPrivateProperty('uri'));
        $this->assertSame(self::OPTIONS, $this->getPrivateProperty('options'));
        $this->assertSame($fetchConfiguration, $this->getPrivateProperty('fetchConfiguration'));
    }

    #[Testing]
    public function getFetchConfiguration(): void
    {
        $fetchConfiguration = new FetchConfiguration(self::CONFIGURATION);

        $fetch = (new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS))
            ->setFetchConfiguration($fetchConfiguration);

        $this->assertSame($fetchConfiguration, $fetch->getFetchConfiguration());
    }

    #[Testing]
    public function setFetchConfiguration(): void
    {
        $fetchConfiguration = new FetchConfiguration(self::CONFIGURATION);

        $fetch = new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS);

        $this->assertInstanceOf(Fetch::class, $fetch->setFetchConfiguration($fetchConfiguration));
        $this->assertSame($fetchConfiguration, $fetch->getFetchConfiguration());
    }

    #[Testing]
    public function getHttpMethod(): void
    {
        $fetch = new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS);

        $this->assertSame(self::HTTP_METHOD, $fetch->getHttpMethod());
    }

    #[Testing]
    public function getUri(): void
    {
        $fetch = new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS);

        $this->assertSame(self::URI, $fetch->getUri());
    }

    #[Testing]
    public function getOptions(): void
    {
        $fetch = new Fetch(self::HTTP_METHOD, self::URI, self::OPTIONS);

        $this->assertSame(self::OPTIONS, $fetch->getOptions());
    }
}
