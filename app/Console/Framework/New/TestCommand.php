<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command {

    use ClassPath;

	protected static $defaultName = 'new:test';

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription('Command required for the creation of new test')
            ->addArgument('test', InputArgument::OPTIONAL, 'Test name', "ExampleTest");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $test = $input->getArgument('test');
		$list = $this->export("tests/", $test);
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		$this->create($url_folder, $list['class']);
		$this->add("<?php\n\n");
		$this->add("namespace {$list['namespace']};\n\n");
		$this->add("use PHPUnit\Framework\TestCase;\n\n");

        $this->add(
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

        $this->force();
        $this->close();

        $output->writeln("<comment>\t>>  TEST: {$test}</comment>");
        $output->writeln("<info>\t>>  TEST: The '{$list['namespace']}\\{$list['class']}' test has been generated</info>");

        return Command::SUCCESS;
    }

}
