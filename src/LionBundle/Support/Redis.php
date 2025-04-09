<?php

declare(strict_types=1);

namespace Lion\Bundle\Support;

use Predis\Client;

/**
 * Process Manager with Redis Driver
 *
 * @package Lion\Bundle\Helpers
 */
class Redis
{
    /**
     * [Zero time for redis processes]
     *
     * @const REDIS_TIME_EMPTY
     */
    private const int REDIS_TIME_EMPTY = 0;

    /**
     * [Client class used for connecting and executing commands on Redis]
     *
     * @var Client $client
     */
    protected Client $client;

    /**
     * [Time in seconds to expire the cache]
     *
     * @var int
     */
    private int $seconds = self::REDIS_TIME_EMPTY;

    /**
     * Class Constructor
     *
     * @param array{
     *     scheme?: string,
     *     host?: string,
     *     port?: int|string,
     *     parameters?: array{
     *         password?: string,
     *         database?: int|string
     *     }
     * } $options [Configuration data for connecting to Redis]
     */
    public function __construct(array $options = [])
    {
        $this->client = new Client(!empty($options) ?  $options : [
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
     * @return mixed
     */
    private function toArray(string $data): mixed
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

        $this->client->set($key, $jsonData);

        if ($this->seconds > self::REDIS_TIME_EMPTY) {
            $this->client->expire($key, $this->seconds);
        }

        $this->seconds = self::REDIS_TIME_EMPTY;

        return $this;
    }

    /**
     * Gets the data stored in cache
     *
     * @param string $key [Index name]
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $this->connect();

        $key = trim($key);

        /** @var string $value */
        $value = $this->client->get($key);

        return empty($value) ? null : $this->toArray($value);
    }

    /**
     * Removes a value from the Redis database
     *
     * @param string $key [Index name]
     *
     * @return Redis
     */
    public function del(string $key): Redis
    {
        $this->connect();

        $key = trim($key);

        $this->client->del($key);

        return $this;
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
