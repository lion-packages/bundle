<?php

namespace App\Console\Framework\Route;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use App\Traits\Framework\PostmanCollector;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PostmanCollectionCommand extends Command
{
    use ClassPath, PostmanCollector, ConsoleOutput;

	protected static $defaultName = "route:postman";
    private array $routes;
    private string $json_name;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->init(env->SERVER_URL);
        $this->json_name = str->of(date('Y_m_d') . "_lion_collection")->lower()->get();
        $this->routes = fetch('GET', env->SERVER_URL . "/route-list");
        array_pop($this->routes);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Command required to create postman collections in JSON format");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rules = require_once("./routes/rules.php");
        $this->addRoutes($this->routes, $rules);
        $path = storage_path("postman/", false);

        Store::folder($path);
        $this->new("{$path}{$this->json_name}", "json");
        $this->add(json_encode([
            'variable' => [
                ['key' => 'base_url', 'value' => env->SERVER_URL, 'type' => "string"]
            ],
            'info' => [
                'name' => env->APP_NAME,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'item' => $this->createCollection($this->getItems()),
            'event' => []
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  COLLECTION: {$this->json_name}"));
        $output->writeln($this->successOutput("\t>>  COLLECTION: Exported in {$path}{$this->json_name}.json"));
        return Command::SUCCESS;
    }
}
