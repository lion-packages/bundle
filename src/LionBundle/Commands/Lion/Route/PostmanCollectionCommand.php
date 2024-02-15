<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use Carbon\Carbon;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Route\Route;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PostmanCollectionCommand extends Command
{
    private ClassFactory $classFactory;
    private PostmanCollection $postmanCollection;
    private Store $store;
    private Str $str;

    private array $routes;
    private string $jsonName;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): PostmanCollectionCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setPostmanCollection(PostmanCollection $postmanCollection): PostmanCollectionCommand
    {
        $this->postmanCollection = $postmanCollection;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): PostmanCollectionCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): PostmanCollectionCommand
    {
        $this->str = $str;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('route:postman')
            ->setDescription('Command required to create postman collections in JSON format');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchRoutes();
        $this->postmanCollection->addRoutes($this->routes, Routes::getRules());
        $path = storage_path('postman/', false);
        $this->store->folder($path);

        $jsonData = [
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => env->SERVER_URL,
                    'type' => 'string'
                ]
            ],
            'info' => [
                'name' => env->APP_NAME,
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

    private function fetchRoutes(): void
    {
        $this->postmanCollection->init(env->SERVER_URL);
        $this->jsonName = $this->str->of(Carbon::now()->format('Y_m_d'))->concat('_lion_collection')->lower()->get();
        $this->routes = json_decode(fetch(Route::GET, env->SERVER_URL . '/route-list')->getBody()->getContents(), true);

        array_pop($this->routes);
    }
}
