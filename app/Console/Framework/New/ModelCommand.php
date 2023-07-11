<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command {

    use ClassPath, ConsoleOutput;

	protected static $defaultName = 'new:model';

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription('Command required for the creation of new Models')
            ->addArgument('model', InputArgument::OPTIONAL, 'Model name', "ExampleModel");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $model = $input->getArgument('model');
		$list = $this->export("app/Models/", $model);
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		$this->create($url_folder, $list['class']);
		$this->add("<?php\n\n");
		$this->add("namespace {$list['namespace']};\n\n");
		$this->add("use LionDatabase\Drivers\MySQL\MySQL as DB;\n");
        $this->add("use LionDatabase\Drivers\MySQL\Schema;\n\n");
		$this->add("class {$list['class']} {\n\n");
		$this->add("\tpublic function __construct() {\n\t\t\n\t}\n\n");

        foreach (["create", "read", "update", "delete"] as $key => $method) {
            $this->add($this->generateFunctionsModel($method, $list['class']));
        }

        $this->add("}");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  MODEL: {$model}"));
        $output->writeln($this->successOutput("\t>>  MODEL: The '{$list['namespace']}\\{$list['class']}' model has been generated"));
        return Command::SUCCESS;
    }

}
