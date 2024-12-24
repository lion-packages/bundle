<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Http;

/**
 * Defines parameters for consuming HTTP requests with GuzzleHttp
 *
 * @property string $httpMethod [HTTP protocol]
 * @property string $uri [URL to make the request]
 * @property array<string, mixed> $options [Options to send through the request,
 * such as headers or parameters]
 * @property FetchConfiguration|null $fetchConfiguration [Defines the
 * configuration data for making an HTTP request]
 *
 * @package Lion\Bundle\Helpers\Http
 */
class Fetch
{
    /**
     * @var FetchConfiguration|null $fetchConfiguration [Defines the
     * configuration data for making an HTTP request]
     */
    private ?FetchConfiguration $fetchConfiguration = null;

    /**
     * Class Constructor
     *
     * @param string $httpMethod [HTTP protocol]
     * @param string $uri [URL to make the request]
     * @param array<string, mixed> $options [Options to send through the request, such as
     * headers or parameters]
     */
    public function __construct(
        private readonly string $httpMethod,
        private readonly string $uri,
        private readonly array $options = []
    ) {}

    /**
     * Returns an HTTP configuration object
     *
     * @return FetchConfiguration|null
     *
     * @internal
     */
    public function getFetchConfiguration(): ?FetchConfiguration
    {
        return $this->fetchConfiguration;
    }

    /**
     * Adds an HTTP configuration object
     *
     * @param FetchConfiguration $fetchConfiguration [Defines the configuration
     * data for making an HTTP request]
     *
     * @return Fetch
     */
    public function setFetchConfiguration(FetchConfiguration $fetchConfiguration): Fetch
    {
        $this->fetchConfiguration = $fetchConfiguration;

        return $this;
    }

    /**
     * Returns the HTTP method of the HTTP request
     *
     * @return string
     *
     * @internal
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * Returns the URI of the HTTP request
     *
     * @return string
     *
     * @internal
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Returns the data from the HTTP request
     *
     * @return array<string, mixed>
     *
     * @internal
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
