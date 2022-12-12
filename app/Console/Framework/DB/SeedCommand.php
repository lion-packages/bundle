<?php

namespace App\Console\Framework\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use App\Traits\Framework\ClassPath;
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
            'run', null, InputOption::VALUE_REQUIRED, ''
        )->addOption(
            'iterate', null, InputOption::VALUE_REQUIRED, ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $seed = $input->getArgument('seed');
        $run = $input->getOption('run');
        $iterate = $input->getOption('iterate');

        if (empty($run)) {
            $run = false;
        }

        $iterate = empty($iterate) ? 1 : (int) $iterate;
        $run = $run === 'true' ? true : false;

        if (!$run) {
            $output->writeln("<comment>Creating seeder...</comment>");

            $list = ClassPath::export("database/Seeders/", ClassPath::normalize($seed));
            $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
            Manage::folder($url_folder);

            ClassPath::create($url_folder, $list['class']);
            ClassPath::add(Str::of("<?php\r")->ln()->ln()->get());
            ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";\r")->ln()->ln()->get());
            ClassPath::add(Str::of("use LionSQL\Drivers\MySQLDriver as DB;")->ln()->ln()->get());
            ClassPath::add(Str::of("class ")->concat($list['class'])->concat(" {\r")->ln()->ln()->get());
            ClassPath::add("\t/**\n");
            ClassPath::add("\t * ------------------------------------------------------------------------------\n");
            ClassPath::add("\t * Seed the application's database\n");
            ClassPath::add("\t * ------------------------------------------------------------------------------\n");
            ClassPath::add("\t **/\n");
            ClassPath::add("\tpublic function run(): object {\r\n\t\treturn DB::call('stored_procedure', []);\n\t}\r\n\n}");
            ClassPath::force();
            ClassPath::close();

            $output->writeln("<info>Seeder created successfully</info>");
            return Command::SUCCESS;
        }

        $namespace = "Database\\Seeders\\" . Str::of($seed)->replace("/", "\\")->get();

        if (!class_exists($namespace)) {
            $output->writeln("<error>Class does not exist</error>");
            return Command::INVALID;
        }

        for ($i = 0; $i < $iterate; $i++) {
            $requestSeeder = (new $namespace())->run();

            if ($requestSeeder->status === 'database-error') {
                $output->writeln("<error>{$requestSeeder->message}</error>");
                return Command::INVALID;
                break;
            }
        }

        $output->writeln("<info>{$requestSeeder->message}</info>");
        return Command::SUCCESS;
    }

}