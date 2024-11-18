<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use Carbon\Carbon;
use DI\Attribute\Inject;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Bundle\Helpers\Http\Routes;
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
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property PostmanCollection $postmanCollection [PostmanCollection class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\Route
 */
class PostmanCollectionCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [PostmanCollection class object]
     *
     * @var PostmanCollection $postmanCollection
     */
    private PostmanCollection $postmanCollection;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [List of defined web routes]
     *
     * @var array $routes
     */
    private array $routes;

    /**
     * [JSON file name]
     *
     * @var string $jsonName
     */
    private string $jsonName;

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
     * @throws LogicException [When this abstract method is not implemented]
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchRoutes();

        $this->postmanCollection->addRoutes($this->routes);

        $path = storage_path('postman/', false);

        $this->store->folder($path);

        $jsonData = [
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $_ENV['SERVER_URL'],
                    'type' => 'string'
                ]
            ],
            'info' => [
                'name' => $_ENV['APP_NAME'],
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'item' => $this->postmanCollection->createCollection($this->postmanCollection->getItems()),
            'event' => []
        ];

        $this->classFactory
            ->create($this->jsonName, 'json', $path)
            ->add(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
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
        $this->postmanCollection->init($_ENV['SERVER_URL']);

        $this->jsonName = $this->str->of(Carbon::now()->format('Y_m_d'))->concat('_lion_collection')->lower()->get();

        $this->routes = json_decode(
            fetch(Route::GET, ($_ENV['SERVER_URL'] . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])->getBody()->getContents(),
            true
        );

        array_pop($this->routes);
    }
}
