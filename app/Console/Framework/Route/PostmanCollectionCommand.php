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

	protected function initialize(InputInterface $input, OutputInterface $output) {
        PostmanCollector::init(env->SERVER_URL);
        $this->routes = fetch('GET', env->SERVER_URL . "/route-list");
        array_pop($this->routes);
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription("Command required to create postman collections in JSON format");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        foreach($this->routes as $key_items => $route) {
            foreach ($route as $key_route => $item) {
                $name = $key_items === "" ? "index" : $key_items;
                PostmanCollector::add($name, $key_items, $key_route);
            }
        }

        $json_name = Str::of(date('Y-m-d') . "_lion_collection")->lower();
        $path = storage_path("postman/", false);
        Store::folder($path);
        ClassPath::new("{$path}{$json_name}", "json");

        ClassPath::add(json->encode([
            'info' => [
                'name' => "Lion-Framework",
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => env->SERVER_URL, 'type' => 'string']
            ],
            'item' => PostmanCollector::get()
        ]));

        ClassPath::force();
        ClassPath::close();

        return Command::SUCCESS;
    }

}