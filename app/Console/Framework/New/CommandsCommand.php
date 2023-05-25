<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;

class CommandsCommand extends Command {

	protected static $defaultName = "new:command";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating command...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
            'Command required for the creation of new Commands'
        )->addArgument(
            'new-command', InputArgument::REQUIRED, 'Command name', null
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Console/Commands/", $input->getArgument('new-command'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\n\n");
        ClassPath::add("namespace {$list['namespace']};\n\n");
        ClassPath::add("use Symfony\Component\Console\Command\Command;\n");
        ClassPath::add("use Symfony\Component\Console\Input\InputInterface;\n");
        ClassPath::add("use Symfony\Component\Console\Output\OutputInterface;\n\n");
        ClassPath::add("class {$list['class']} extends Command {\n\n");
        ClassPath::add("\t" . 'protected static $defaultName = "";' . "\n\n");
        ClassPath::add("\t" . 'protected function initialize(InputInterface $input, OutputInterface $output) {' . "\n\n\t}\n\n");
        ClassPath::add("\t" . 'protected function interact(InputInterface $input, OutputInterface $output) {' . "\n\n\t}\n\n");
        ClassPath::add("\t" . "protected function configure() {\n\t\t" . '$this->setDescription("");' . "\n\t}\n\n");
        ClassPath::add("\t" . 'protected function execute(InputInterface $input, OutputInterface $output) {' . "\n");
        ClassPath::add("\t\t" . '$output->writeln("");' . "\n\t\t" . 'return Command::SUCCESS;' . "\n");
        ClassPath::add("\t}\n\n}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Command created successfully</info>");
        return Command::SUCCESS;
	}

}
