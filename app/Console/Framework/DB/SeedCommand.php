<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use LionFiles\Store;
use LionHelpers\Str;

class SeedCommand extends Command {

	protected static $defaultName = "db:seed";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            "Command required for creating new seeds"
        )->addArgument(
            'seed', InputArgument::REQUIRED
        )->addOption(
            'run', null, InputOption::VALUE_REQUIRED, 'Do you want to run the seeder?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $seed = $input->getArgument('seed');
        $run = $input->getOption('run');

        if (empty($run)) {
            $run = false;
        }

        $run = $run === 'true' ? true : false;
        if (!$run) {
            $output->writeln("<comment>Creating seeder...</comment>");

            $class = ClassPath::export("database/Seeders/", $seed);
            $url_folder = lcfirst(Str::of($class['namespace'])->replace("\\", "/")->get());
            Store::folder($url_folder);

            ClassPath::create($url_folder, $class['class']);
            ClassPath::add(Str::of("<?php")->ln()->ln()->get());
            ClassPath::add(Str::of("namespace ")->concat($class['namespace'])->concat(";")->ln()->ln()->get());
            ClassPath::add(Str::of("use LionSQL\Drivers\MySQL as DB;")->ln()->ln()->get());
            ClassPath::add(Str::of("class ")->concat($class['class'])->concat(" {")->ln()->ln()->get());
            ClassPath::add("\t/**\n");
            ClassPath::add("\t * ------------------------------------------------------------------------------\n");
            ClassPath::add("\t * Seed the application's database\n");
            ClassPath::add("\t * ------------------------------------------------------------------------------\n");
            ClassPath::add("\t **/\n");
            ClassPath::add("\tpublic function run(): array|object {\n\t\treturn DB::call('stored_procedure', [])->execute();\n\t}\n\n}");
            ClassPath::force();
            ClassPath::close();

            $output->writeln("<info>Seeder created successfully</info>");
            return Command::SUCCESS;
        }

        $namespace = Str::of($seed)->replace("/", "\\")->get();
        if (!class_exists($namespace)) {
            $output->writeln("<error>Class does not exist</error>");
            return Command::INVALID;
        }

        $requestSeeder = (new $namespace())->run();
        if (!isset($requestSeeder->status)) {
            (new Table($output))
                ->setHeaders($requestSeeder['columns'])
                ->setRows($requestSeeder['rows'])
                ->render();
        } else {
            if ($requestSeeder->status === 'database-error') {
                $output->writeln("<error>{$requestSeeder->message}</error>");
                return Command::INVALID;
            }

            $output->writeln("<info>{$requestSeeder->message}</info>");
        }

        return Command::SUCCESS;
    }

}