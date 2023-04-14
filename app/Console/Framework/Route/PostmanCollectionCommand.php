<?php

namespace App\Console\Framework\Route;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Traits\Framework\ClassPath;
use App\Traits\Framework\PostmanCollector;
use LionFiles\Store;
use LionHelpers\Str;

class PostmanCollectionCommand extends Command {

	protected static $defaultName = "route:postman";
    private array $routes;
    private string $json_name;

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Exporting collection...</comment>");

        PostmanCollector::init(env->SERVER_URL);
        $this->json_name = Str::of(date('Y-m-d') . "_lion_collection")->lower();
        $this->routes = fetch('GET', env->SERVER_URL . "/route-list");
        array_pop($this->routes);
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription("Command required to create postman collections in JSON format");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        PostmanCollector::addRoutes($this->routes, rules);
        PostmanCollector::generateItems();
        $items = PostmanCollector::getItems();
        $path = storage_path("postman/", false);

        Store::folder($path);
        ClassPath::new("{$path}{$this->json_name}", "json");
        ClassPath::add(json->encode([
            'variable' => [
                ['key' => 'base_url', 'value' => env->SERVER_URL, 'type' => "string"]
            ],
            'info' => [
                'name' => env->APP_NAME,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'item' => PostmanCollector::createCollection($items),
            'event' => []
        ]));
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Exported collection: {$path}{$this->json_name}.json</info>");
        return Command::SUCCESS;
    }

}