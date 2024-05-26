<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\EnviromentProviderTrait;
use Tests\Providers\Helpers\PostmanCollectionProviderTrait;

class PostmanCollectionTest extends Test
{
    use EnviromentProviderTrait;
    use PostmanCollectionProviderTrait;

    const HOST = 'http://127.0.0.1:8000';
    const POSTMAN_CONFIG = [
        'params' => [
            'routes' => [],
            'items' => [],
            'host' => [
                'url' => self::HOST,
                'params' => [
                    'host' => ["{{base_url}}"]
                ]
            ]
        ]
    ];

    private PostmanCollection $postmanCollection;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $this->postmanCollection = (new Container())
            ->injectDependencies(new PostmanCollection());

        $this->initReflection($this->postmanCollection);
    }

    public function testInit(): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));
    }

    #[DataProvider('addValuesParamProvider')]
    public function testAddValuesParam(string $params, string $value, string $key, bool $index, string $return): void
    {
        $returnMethod = $this->getPrivateMethod('addValuesParam', [$params, $value, $key, $index]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('createQueryParamsProvider')]
    public function testCreateQueryParams(string $jsonParams, array $return): void
    {
        $returnMethod = $this->getPrivateMethod('createQueryParams', [$jsonParams]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addParamsProvider')]
    public function testAddParams(array $rules, array|string $return): void
    {
        $returnMethod = $this->getPrivateMethod('addParams', [$rules]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addPatchProvider')]
    public function testAddPatch(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPatch', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addGetProvider')]
    public function testAddGet(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addGet', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addDeleteProvider')]
    public function testAddDelete(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addDelete', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addPostProvider')]
    public function testAddPost(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPost', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addPutProvider')]
    public function testAddPut(string $name, string $route, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addPut', [$name, $route, $params]);

        $this->assertSame($return, $returnMethod);
    }

    #[DataProvider('addRequestProvider')]
    public function testAddRequest(string $name, string $route, string $method, array $params, array $return): void
    {
        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $returnMethod = $this->getPrivateMethod('addRequest', [$name, $route, $method, $params]);

        $this->assertSame($return, $returnMethod);
    }

    public function testAddRoutes(): void
    {
        $routes = json_decode(
            fetch(Http::GET, (self::HOST . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes, Routes::getRules());

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);
    }

    public function testCreateCollection(): void
    {
        $routes = json_decode(
            fetch(Http::GET, (self::HOST . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes, Routes::getRules());

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);

        $collection = $this->postmanCollection->createCollection($this->postmanCollection->getItems());

        $jsonProvider = json_decode(
            (new Store())->get('./tests/Providers/Helpers/Commands/PostmanProvider.json'),
            true
        );

        $this->assertJsonStringEqualsJsonString(json_encode($jsonProvider['item']), json_encode($collection));
    }

    public function testGetRoutes(): void
    {
        $routes = json_decode(
            fetch(Http::GET, (self::HOST . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes, Routes::getRules());

        $addRoutes = json_encode($this->getPrivateProperty('postman'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/AddRoutesProvider.json', $addRoutes);

        $getRoutes = json_encode($this->postmanCollection->getRoutes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/Helpers/Commands/GetRoutesProvider.json', $getRoutes);
    }

    public function testGetItems(): void
    {
        $routes = json_decode(
            fetch(Http::GET, (self::HOST . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])
                ->getBody()
                ->getContents(),
            true
        );

        array_pop($routes);

        $this->postmanCollection->init(self::HOST);

        $this->assertSame(self::POSTMAN_CONFIG, $this->getPrivateProperty('postman'));

        $this->postmanCollection->addRoutes($routes, Routes::getRules());

        $jsonProvider = json_decode((new Store())->get('./tests/Providers/Helpers/Commands/AddRoutesProvider.json'), true);

        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonProvider['params']['items']),
            json_encode($this->postmanCollection->getItems())
        );
    }

    #[DataProvider('reverseArrayProvider')]
    public function testReverseArray(array $items, array $return): void
    {
        $returnMethod = $this->getPrivateMethod('reverseArray', [$items]);

        $this->assertSame($return, $returnMethod);
    }
}
