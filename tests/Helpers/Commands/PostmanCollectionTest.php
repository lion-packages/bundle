<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Bundle\Support\Http\Fetch;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Providers\Helpers\PostmanCollectionProviderTrait;

class PostmanCollectionTest extends Test
{
    use PostmanCollectionProviderTrait;

    private const string HOST = 'http://localhost:8000';
    private const array POSTMAN_CONFIG = [
        'params' => [
            'host' => [
                'url' => self::HOST,
                'params' => [
                    'host' => [
                        "{{base_url}}",
                    ],
                ],
            ],
        ],
    ];

    private PostmanCollection $postmanCollection;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $this->postmanCollection = new Container()->resolve(PostmanCollection::class);

        $this->initReflection($this->postmanCollection);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function init(): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addValuesParamProvider')]
    public function addValuesParam(string $params, string $value, string $key, bool $index, string $return): void
    {
        $returnMethod = $this->getPrivateMethod('addValuesParam', [$params, $value, $key, $index]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('createQueryParamsProvider')]
    public function createQueryParams(string $jsonParams, array $return): void
    {
        $returnMethod = $this->getPrivateMethod('createQueryParams', [$jsonParams]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addParamsProvider')]
    public function addParams(array $rules, array|string $return): void
    {
        $returnMethod = $this->getPrivateMethod('addParams', [
            'rules' => $rules,
        ]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addPatchProvider')]
    public function addPatch(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPatch', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addGetProvider')]
    public function addGet(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addGet', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addDeleteProvider')]
    public function addDelete(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addDelete', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addPostProvider')]
    public function addPost(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPost', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addPutProvider')]
    public function addPut(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPut', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addRequestProvider')]
    public function addRequest(string $name, string $route, string $method, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addRequest', [$name, $route, $method, $params]);

        $this->assertSame($return, $returnMethod);
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     */
    #[Testing]
    public function addRoutes(): void
    {
        $routes = json_decode(
            fetch(
                new Fetch(Http::GET, (self::HOST . '/route-list'), [
                    'headers' => [
                        'Lion-Auth' => $_ENV['SERVER_HASH'],
                    ],
                ])
            )
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes);

        $this->postmanCollection->generateItems();

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     */
    #[Testing]
    public function createCollection(): void
    {
        $routes = json_decode(
            fetch(
                new Fetch(Http::GET, (self::HOST . '/route-list'), [
                    'headers' => [
                        'Lion-Auth' => $_ENV['SERVER_HASH'],
                    ],
                ])
            )
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes);

        $this->postmanCollection->generateItems();

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);

        $collection = $this->postmanCollection->createCollection($this->postmanCollection->getItems());

        $jsonProvider = json_decode(
            new Store()->get('./tests/Providers/Helpers/Commands/PostmanProvider.json'),
            true
        );

        $this->assertJsonStringEqualsJsonString(json_encode($jsonProvider['item']), json_encode($collection));
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     */
    #[Testing]
    public function getRoutes(): void
    {
        $routes = json_decode(
            fetch(
                new Fetch(Http::GET, (self::HOST . '/route-list'), [
                    'headers' => [
                        'Lion-Auth' => $_ENV['SERVER_HASH'],
                    ],
                ])
            )
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes);

        $this->postmanCollection->generateItems();

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);

        $getRoutes = json_encode($this->postmanCollection->getRoutes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/GetRoutesProvider.json', $getRoutes);
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     */
    #[Testing]
    public function getItems(): void
    {
        $routes = json_decode(
            fetch(
                new Fetch(Http::GET, (self::HOST . '/route-list'), [
                    'headers' => [
                        'Lion-Auth' => $_ENV['SERVER_HASH'],
                    ],
                ])
            )
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes);

        $this->postmanCollection->generateItems();

        $jsonProvider = json_decode(new Store()->get('./tests/Providers/Helpers/Commands/AddRoutesProvider.json'), true);

        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonProvider['params']['items']),
            json_encode($this->postmanCollection->getItems())
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('reverseArrayProvider')]
    public function reverseArray(array $items, array $return): void
    {
        $returnMethod = $this->getPrivateMethod('reverseArray', [$items]);

        $this->assertSame($return, $returnMethod);
    }
}
