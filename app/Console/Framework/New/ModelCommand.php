<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;

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
			'model', InputArgument::REQUIRED, '', null
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Models/", $input->getArgument('model'));
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\n\n");
		ClassPath::add("namespace {$list['namespace']};\n\n");
		ClassPath::add("use LionSQL\Drivers\MySQL as DB;\n\n");
		ClassPath::add("class {$list['class']} {\n\n");
		ClassPath::add("\tpublic function __construct() {\n\t\t\n\t}\n\n");
        ClassPath::add("\tpublic function createDB() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function readDB() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function updateDB() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function deleteDB() {\n\n\t}\n\n");
        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Model created successfully</info>");
        return Command::SUCCESS;
    }

}