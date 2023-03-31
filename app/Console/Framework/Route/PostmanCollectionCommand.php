<?php

namespace App\Console\Framework\Route;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Traits\Framework\ClassPath;

class PostmanCollectionCommand extends Command {

	protected static $defaultName = "route:postman";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription("");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $postman_id =  function ($data = null) : string {
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        };

        $routes = fetch('GET', env->SERVER_URL . "/route-list");
        array_pop($routes);
        vd($routes);
        $items = [];

        foreach($items as $key => $route) {
            foreach ($route as $key_route => $item) {
                echo($key_route);
            }
        }

        ClassPath::new(env->APP_NAME . ".postman_collection", "json");
        ClassPath::add(str_replace("\/", "/", json->encode([
            'info' => [
                '_postman_id' => $postman_id(),
                'name' => "Lion-Example",
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => env->SERVER_URL, 'type' => 'string']
            ],
            'item' => [
                [
                    'name' => 'index',
                    'response' => [],
                    'request' => [
                        'method' => 'POST',
                        'header' => [],
                        'body' => [],
                        'url' => [
                            'raw' => '{{base_url}}/api',
                            'host' => "{{base_url}}",
                            'path' => [
                                "api"
                            ]
                        ]
                    ]
                ]
            ]
        ])));
        ClassPath::force();
        ClassPath::close();

        return Command::SUCCESS;
    }

}