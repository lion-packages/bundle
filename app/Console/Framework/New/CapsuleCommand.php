<?php

namespace App\Console\Framework\New;

use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Traits\Framework\ClassPath;
use LionHelpers\Str;

class CapsuleCommand extends Command {

	protected static $defaultName = "new:capsule";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating capsule...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
            "Command required for creating new custom capsules"
        )->addArgument(
            'capsule', InputArgument::REQUIRED, '', null
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("database/Class/", $input->getArgument('capsule'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add(Str::of("<?php\r")->ln()->ln()->get());
        ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";\r")->ln()->ln()->get());
        ClassPath::add(Str::of("class ")->concat($list['class'])->concat(" {\r")->ln()->ln()->concat("}")->get());
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Capsule created successfully</info>");
        return Command::SUCCESS;
	}

}