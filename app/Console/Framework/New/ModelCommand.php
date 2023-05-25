<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command {

	protected static $defaultName = 'new:model';

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$output->writeln("<comment>Creating model...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			'Command required for the creation of new Models'
		)->addArgument(
			'model', InputArgument::REQUIRED, 'Model name', null
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Models/", $input->getArgument('model'));
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\n\n");
		ClassPath::add("namespace {$list['namespace']};\n\n");
		ClassPath::add("use LionSQL\Drivers\MySQL\MySQL as DB;\n");
        ClassPath::add("use LionSQL\Drivers\MySQL\Schema;\n\n");
		ClassPath::add("class {$list['class']} {\n\n");
		ClassPath::add("\tpublic function __construct() {\n\t\t\n\t}\n\n");

        foreach (["create", "read", "update", "delete"] as $key => $method) {
            ClassPath::add(ClassPath::generateFunctionsModel($method, $list['class']));
        }

        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Model created successfully</info>");
        return Command::SUCCESS;
    }

}
