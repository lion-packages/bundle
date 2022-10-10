<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionCommand\Functions\{ FILES, ClassPath };

class CapsuleCommand extends Command {

	protected static $defaultName = "new:capsule";
    private string $default_path = "app/Models/Class/";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating capsule...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of new Capsules'
        )->addArgument(
            'capsule', InputArgument::REQUIRED, '', null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $list = ClassPath::export(
            $this->default_path,
            $input->getArgument('capsule')
        );

        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        FILES::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\r\n\n");
        ClassPath::add("namespace {$list['namespace']};\r\n\n");
        ClassPath::add("class {$list['class']} {\r\n\n}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Capsule created successfully</info>");
        return Command::SUCCESS;
    }

}