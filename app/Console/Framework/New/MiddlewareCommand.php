<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;

class MiddlewareCommand extends Command {

	protected static $defaultName = 'new:middleware';

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$output->writeln("<comment>Creating middleware...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			'Command required for the creation of new Middleware'
		)->addArgument(
			'middleware', InputArgument::REQUIRED, 'Middleware name', null
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Http/Middleware/", $input->getArgument('middleware'));
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\n\n");
		ClassPath::add("namespace {$list['namespace']};\n\n");
		ClassPath::add("class {$list['class']} {\n\n");
		ClassPath::add("\tpublic function __construct() {\n\n\t}\n\n}");
		ClassPath::force();
		ClassPath::close();

		$output->writeln("<info>Middleware created successfully</info>");
		return Command::SUCCESS;
	}

}
