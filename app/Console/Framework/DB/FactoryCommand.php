<?php

namespace App\Console\Framework\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use App\Traits\Framework\ClassPath;
use LionHelpers\Str;

class FactoryCommand extends Command {

	protected static $defaultName = "db:factory";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of new factories'
        )->addArgument(
            'factory', InputArgument::REQUIRED
        )->addOption(
            'path', null, InputOption::VALUE_REQUIRED, 'Do you want to configure your own route?'
        )->addOption(
            'message', null, InputOption::VALUE_REQUIRED, ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $factory = $input->getArgument('factory');
        $message = $input->getOption("message");
        $path = $input->getOption("path");

        if ($message === null) {
            $message = true;
        }

        if ($message) {
            $output->writeln("<comment>Creating factory...</comment>");
        }

        if ($path === null) {
            $path = "";
        }

        $list = ClassPath::export(
            "Database/Factories/",
            ($path . ClassPath::normalize($factory))
        );

        $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
        Manage::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add(Str::of("<?php\r")->ln()->ln()->get());
        ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";")->ln()->ln()->get());
        ClassPath::add(Str::of("use Faker\Factory;")->ln()->ln()->get());
        ClassPath::add(Str::of("class ")->concat($list['class'])->concat(" {\r")->ln()->ln()->get());
        ClassPath::add("\t/**\n");
        ClassPath::add("\t * ------------------------------------------------------------------------------\n");
        ClassPath::add("\t * Define the model's default state\n");
        ClassPath::add("\t * ------------------------------------------------------------------------------\n");
        ClassPath::add("\t **/\n");
        ClassPath::add("\tpublic static function definition(): array {\n\t\t" . '$faker = Factory::create();' . "\n\n\t\treturn [];\n\t}\n\n");
        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        if ($message) {
            $output->writeln("<info>Factory created successfully</info>");
        }

        return Command::SUCCESS;
    }

}