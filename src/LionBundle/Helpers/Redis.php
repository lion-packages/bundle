<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Predis\Client;

/**
 * Process Manager with Redis Driver
 *
 * @property Client $client [Client class used for connecting and executing
 *  commands on Redis]
 * @property int $seconds [Time in seconds to expire the cache]
 *
 * @package Lion\Bundle\Helpers
 */
class Redis
{
    /**
     * Default seconds for redis cache
     *
     * @const REDIS_DEFAULT_SECONDS
     */
    public const int REDIS_DEFAULT_SECONDS = 15;

    /**
     * [Client class used for connecting and executing commands on Redis]
     *
     * @var Client $client
     */
    private Client $client;

    /**
     * [Time in seconds to expire the cache]
     *
     * @var int
     */
    private int $seconds = 0;

    /**
     * Class Constructor
     */
    public function __construct(array $options = [])
    {
        $this->client =  new Client(!empty($options) ?  $options : [
            'scheme' => env('REDIS_SCHEME'),
            'host' => env('REDIS_HOST'),
            'port' => env('REDIS_PORT'),
            'parameters' => [
                'password' => env('REDIS_PASSWORD'),
                'database' => env('REDIS_DATABASES'),
            ],
        ]);
    }

    /**
     * Gets the Client object
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Make the connection with the Redis Driver
     *
     * @return void
     */
    private function connect(): void
    {
        $this->client->connect();
    }

    /**
     * Converts string in JSON format to array
     *
     * @param string $data [String in JSON format]
     *
     * @return array
     */
    private function toArray(string $data): array
    {
        return json_decode($data, true);
    }

    /**
     * Set the cache time limit with Redis
     *
     * @param int $seconds [Cache timeout with Redis]
     *
     * @return $this
     */
    public function setTime(int $seconds): Redis
    {
        $this->seconds = $seconds;

        return $this;
    }

    /**
     * Store data in cache
     *
     * @param string $key [Index name]
     * @param mixed $value [Defined value]
     *
     * @return Redis
     */
    public function set(string $key, mixed $value): Redis
    {
        $this->connect();

        $key = trim($key);

        $jsonData = json_encode($value);

        $this->seconds = 0 === $this->seconds ? self::REDIS_DEFAULT_SECONDS : $this->seconds;

        $this->client->set($key, $jsonData);

        $this->client->expire($key, $this->seconds);

        $this->seconds = self::REDIS_DEFAULT_SECONDS;

        return $this;
    }

    /**
     * Gets the data stored in cache
     *
     * @param string $key [Index name]
     *
     * @return array|null
     */
    public function get(string $key): ?array
    {
        $this->connect();

        $key = trim($key);

        if (!$this->client->exists($key)) {
            return null;
        }

        return $this->toArray($this->client->get($key));
    }

    /**
     * Clear all cached data
     *
     * @return void
     */
    public function empty(): void
    {
        $this->connect();

        $this->client->flushall();
    }
}
