<?php

namespace App\Console\Framework\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;
use LionHelpers\Str;

class FactoryCommand extends Command {

	protected static $defaultName = "db:factory";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating factory...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of new factories'
        )->addArgument(
            'factory', InputArgument::REQUIRED
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $list = ClassPath::export("Database/Factories/", $input->getArgument('factory'));
        $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
        Store::folder($url_folder);

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

        $output->writeln("<info>Factory created successfully</info>");
        return Command::SUCCESS;
    }

}