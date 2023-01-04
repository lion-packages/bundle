<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
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
		Manage::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\r\n\n");
		ClassPath::add("namespace {$list['namespace']};\r\n\n");
		ClassPath::add("use LionSQL\Drivers\MySQL as DB;\r\n\n");
		ClassPath::add("class {$list['class']} {\r\n\n");
		ClassPath::add("\tpublic function __construct() {\r\n\t\t\r\n\t}");
		ClassPath::add("\r\n\n}");
		ClassPath::force();
		ClassPath::close();

		$output->writeln("<info>Model created successfully</info>");
		return Command::SUCCESS;
	}

}