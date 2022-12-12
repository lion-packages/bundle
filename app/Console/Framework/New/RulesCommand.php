<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use App\Traits\Framework\ClassPath;

class RulesCommand extends Command {

    protected static $defaultName = "new:rule";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating rule...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for rule creation'
        )->addArgument(
            'rule', InputArgument::REQUIRED, '', null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $list = ClassPath::export("app/Rules/", $input->getArgument('rule'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Manage::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\r\n\n");
        ClassPath::add("namespace {$list['namespace']};\r\n\n");
        ClassPath::add("use App\Traits\Framework\ShowErrors;\r\n\n");
        ClassPath::add("class {$list['class']} {\r\n\n");
        ClassPath::add("\tuse ShowErrors;\n\n");
        // ClassPath::add("\tpublic function __construct() {\r\n\n\t}\r\n\n");
        ClassPath::add("\tpublic static function passes(): void {\n");
        ClassPath::add("\t\t" . 'self::validate(function(\Valitron\Validator $validator) {');
        ClassPath::add("\n\t\t\t" . '$validator->rule("", "")->message("");' . "\n\t\t});\n");
        ClassPath::add("\t}\r\n\n}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Rule created successfully</info>");
        return Command::SUCCESS;
    }

}