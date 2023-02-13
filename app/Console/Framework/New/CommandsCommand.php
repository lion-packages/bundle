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
            'new-command', InputArgument::REQUIRED, '', null
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Console/", $input->getArgument('new-command'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\r\n\n");
        ClassPath::add("namespace {$list['namespace']};\r\n\n");
        ClassPath::add("use Symfony\Component\Console\Command\Command;\r\n");
        ClassPath::add("use Symfony\Component\Console\Input\InputInterface;\r\n");
        ClassPath::add("use Symfony\Component\Console\Output\OutputInterface;\r\n\n");
        ClassPath::add("class {$list['class']} extends Command {\r\n\n");
        ClassPath::add("\t" . 'protected static $defaultName = "";' . "\r\n\n");
        ClassPath::add("\t" . 'protected function initialize(InputInterface $input, OutputInterface $output) {' . "\r\n\n\t}\r\n\n");
        ClassPath::add("\t" . 'protected function interact(InputInterface $input, OutputInterface $output) {' . "\r\n\n\t}\r\n\n");
        ClassPath::add("\t" . "protected function configure() {\r\n\t\t" . '$this->setDescription("");' . "\r\n\t}\r\n\n");
        ClassPath::add("\t" . 'protected function execute(InputInterface $input, OutputInterface $output) {' . "\r\n");
        ClassPath::add("\t\t" . '$output->writeln("");' . "\r\n\t\t" . 'return Command::SUCCESS;' . "\r\n");
        ClassPath::add("\t}\r\n\n}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Command created successfully</info>");
        return Command::SUCCESS;
	}

}