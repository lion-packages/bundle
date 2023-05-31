<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;

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
			'test', InputArgument::REQUIRED, 'Test name', null
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("tests/", $input->getArgument('test'));
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		ClassPath::create($url_folder, $list['class']);
		ClassPath::add("<?php\n\n");
		ClassPath::add("namespace {$list['namespace']};\n\n");
		ClassPath::add("use PHPUnit\Framework\TestCase;\n\n");

        ClassPath::add(
            Str::of("class ")
                ->concat($list['class'])
                ->concat(" extends TestCase {")->ln()->ln()->lt()
                ->concat("public function setUp(): void {")->ln()->ln()->lt()
                ->concat("}")->ln()->ln()->lt()
                ->concat("public function testExample() {")->ln()->ln()->lt()
                ->concat("}")->ln()->ln()
                ->concat("}")
                ->get()
        );

        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Test created successfully</info>");
        return Command::SUCCESS;
    }

}
