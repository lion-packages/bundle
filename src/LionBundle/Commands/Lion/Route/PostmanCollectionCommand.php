<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use DI\Attribute\Inject;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Bundle\Support\Http\Fetch;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Route\Route;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate JSON object structure for POSTMAN collections
 *
 * @package Lion\Bundle\Commands\Lion\Route
 */
class PostmanCollectionCommand extends Command
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Generate structures to create Postman collections]
     *
     * @var PostmanCollection $postmanCollection
     */
    private PostmanCollection $postmanCollection;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [List of defined web routes]
     *
     * @var array{
     *     array<string, array{
     *          filters: array<int, string>,
     *          handler: array{
     *              controller: bool|array{
     *                  name: string,
     *                  function: string
     *              },
     *              callback: bool
     *          }
     *     }>
     * } $routes
     */
    private array $routes;

    /**
     * [JSON file name]
     *
     * @var string $jsonName
     */
    private string $jsonName;

    /**
     * [Server URL]
     *
     * @var string $serverUrl
     */
    private string $serverUrl;

    /**
     * [Application Name]
     *
     * @var string $appName
     */
    private string $appName;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): PostmanCollectionCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setPostmanCollection(PostmanCollection $postmanCollection): PostmanCollectionCommand
    {
        $this->postmanCollection = $postmanCollection;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): PostmanCollectionCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): PostmanCollectionCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('route:postman')
            ->setDescription('Command required to create postman collections in JSON format');
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $serverUrl */
        $serverUrl = env('SERVER_URL');

        /** @var string $appName */
        $appName = env('APP_NAME');

        $this->serverUrl = $serverUrl;

        $this->appName = $appName;
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int
     *
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchRoutes();

        /** @phpstan-ignore-next-line */
        $this->postmanCollection->addRoutes($this->routes);

        $this->postmanCollection->generateItems();

        $path = storage_path('postman/');

        $this->store->folder($path);

        $jsonData = [
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $this->serverUrl,
                    'type' => 'string'
                ]
            ],
            'info' => [
                'name' => $this->appName,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            /** @phpstan-ignore-next-line */
            'item' => $this->postmanCollection->createCollection($this->postmanCollection->getItems()),
            'event' => []
        ];

        /** @var non-empty-string $json */
        $json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->classFactory
            ->create($this->jsonName, 'json', $path)
            ->add($json)
            ->add("\n")
            ->close();

        $output->writeln($this->warningOutput("\t>>  COLLECTION: {$this->jsonName}"));

        $output->writeln($this->successOutput("\t>>  COLLECTION: Exported in {$path}{$this->jsonName}.json"));

        return Command::SUCCESS;
    }

    /**
     * Initialize the parameters for the generation of the collection
     *
     * @return void
     *
     * @throws GuzzleException
     */
    private function fetchRoutes(): void
    {
        /** @var string $serverHash */
        $serverHash = env('SERVER_HASH');

        $this->postmanCollection->init($this->serverUrl);

        /** @var string $jsonName */
        $jsonName = $this->str
            ->of(now()->format('Y_m_d'))
            ->concat('_lion_collection')
            ->lower()
            ->get();

        $this->jsonName = $jsonName;

        $json = fetch(
            new Fetch(Route::GET, "{$this->serverUrl}/route-list", [
                'headers' => [
                    'Lion-Auth' => $serverHash,
                ],
            ])
        )
            ->getBody()
            ->getContents();

        /**
         * @var array{
         *     array<string, array{
         *          filters: array<int, string>,
         *          handler: array{
         *              controller: bool|array{
         *                  name: string,
         *                  function: string
         *              },
         *              callback: bool
         *          }
         *     }>
         * } $routes
         */
        $routes = json_decode($json, true);

        $this->routes = $routes;

        array_pop($this->routes);
    }
}
