<?php

declare(strict_types=1);

namespace Lion\Bundle\Support\Http;

/**
 * Defines the configuration data for making an HTTP request
 *
 * @package Lion\Bundle\Helpers\Http
 */
class FetchConfiguration
{
    /**
     * Class Constructor
     *
     * @param array<string, mixed> $configuration [Configuration data]
     */
    public function __construct(
        private readonly array $configuration = []
    ) {
    }

    /**
     * Returns configuration data
     *
     * @return array<string, mixed>
     *
     * @internal
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
