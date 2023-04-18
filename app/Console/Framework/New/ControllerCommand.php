<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ArrayInput, InputInterface, InputArgument, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;
use LionHelpers\Str;

class ControllerCommand extends Command {

    protected static $defaultName = 'new:controller';

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating controller...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of new Controllers'
        )->addArgument(
            'controller', InputArgument::REQUIRED, '', null
        )->addOption(
            'model', null, InputOption::VALUE_REQUIRED, 'Do you want to create the model?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $model = $input->getOption('model');
        $list = ClassPath::export("app/Http/Controllers/", $input->getArgument('controller'));
        $list_model = null;
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        if ($model != null) {
            $list_model = ClassPath::export("app/models/", $model);
        }

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\n\n");
        ClassPath::add("namespace {$list['namespace']};\n\n");

        if ($model != null) {
            ClassPath::add("use {$list_model['namespace']}\\{$list_model['class']}; \n\n");
        }

        ClassPath::add("class {$list['class']} {\n\n");

        if ($model != null) {
            $camel_class = Str::of($list_model['class'])->camel();
            ClassPath::add(Str::of("\tprivate {$list_model['class']} $")->concat($camel_class)->concat(";")->ln()->ln()->get());
            ClassPath::add("\tpublic function __construct() {\n\t\t" . '$this->' . "{$camel_class} = new {$list_model['class']}();\n\t}\n\n");
        } else {
            ClassPath::add("\tpublic function __construct() {\n\n\t}\n\n");
        }

        ClassPath::add("\tpublic function create() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function read() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function update() {\n\n\t}\n\n");
        ClassPath::add("\tpublic function delete() {\n\n\t}\n\n");
        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Controller created successfully</info>");

        if ($model != null) {
            $this->getApplication()->find('new:model')->run(
                new ArrayInput(['model' => $model]),
                $output
            );
        }

        return Command::SUCCESS;
    }

}