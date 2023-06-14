<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MiddlewareCommand extends Command {

    use ClassPath;

	protected static $defaultName = 'new:middleware';

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription('Command required for the creation of new Middleware')
            ->addArgument('middleware', InputArgument::OPTIONAL, 'Middleware name', "ExampleMiddleware");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $middleware = $input->getArgument('middleware');
		$list = $this->export("app/Http/Middleware/", $middleware);
		$url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
		Store::folder($url_folder);

		$this->create($url_folder, $list['class']);
		$this->add("<?php\n\n");
		$this->add("namespace {$list['namespace']};\n\n");
		$this->add("class {$list['class']} {\n\n");
		$this->add("\tpublic function __construct() {\n\n\t}\n\n}");
		$this->force();
		$this->close();

        $output->writeln("<comment>\t>>  MIDDLEWARE: {$middleware}</comment>");
        $output->writeln("<info>\t>>  MIDDLEWARE: The '{$list['namespace']}\\{$list['class']}' middleware has been generated</info>");

		return Command::SUCCESS;
	}

}
