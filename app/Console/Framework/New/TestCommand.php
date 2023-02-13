<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;

class TestCommand extends Command {

	protected static $defaultName = 'new:test';

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$output->writeln("<comment>Creating test...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			'Command required for the creation of new test'
		)->addArgument(
			'test', InputArgument::REQUIRED, '', null
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("tests/", $input->getArgument('test'));
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\r\n\n");
		ClassPath::add("namespace {$list['namespace']};\r\n\n");
		ClassPath::add("use PHPUnit\Framework\TestCase;\r\n\n");
		ClassPath::add("class {$list['class']} extends TestCase {\r\n\n}");
		ClassPath::force();
		ClassPath::close();

		$output->writeln("<info>Test created successfully</info>");
		return Command::SUCCESS;
	}

}