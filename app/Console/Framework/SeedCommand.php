<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use App\Traits\Framework\ClassPath;

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
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $seed = $input->getArgument('seed');
        $run = $input->getOption('run');

        if (empty($run)) {
            $run = false;
        }

        if ($run === 'true') {
            $run = true;
        } else {
            $run = false;
        }

        if (!$run) {
            $output->writeln("<comment>Creating seeder...</comment>");

            $list = ClassPath::export("database/Seeders/", $seed);
            $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
            Manage::folder($url_folder);

            ClassPath::create($url_folder, $list['class']);
            ClassPath::add("<?php\r\n\n");
            ClassPath::add("namespace {$list['namespace']};\r\n\n");
            ClassPath::add("use LionSQL\Drivers\MySQLDriver as Builder;\n\n");
            ClassPath::add("class {$list['class']} {\r\n\n");
            ClassPath::add("\tpublic function __construct() {\n");
            ClassPath::add("\t\tBuilder::init([\n\t\t\t'host' => env->DB_HOST,\n");
            ClassPath::add("\t\t\t'port' => env->DB_PORT,\n");
            ClassPath::add("\t\t\t'db_name' => env->DB_NAME,\n");
            ClassPath::add("\t\t\t'user' => env->DB_USER,\n");
            ClassPath::add("\t\t\t'password' => env->DB_PASSWORD,\n");
            ClassPath::add("\t\t]);");
            ClassPath::add("\n\t}\n\n");
            ClassPath::add("\tpublic function run(): object {\r\n\t\treturn Builder::call('stored_procedure', []);\n\t}\r\n\n}");
            ClassPath::force();
            ClassPath::close();

            $output->writeln("<info>Seeder created successfully</info>");
            return Command::SUCCESS;
        }

        $namespace = "Database\\Seeders\\" . str_replace("/", "\\", $seed);
        $exist_class = class_exists($namespace);

        if (!$exist_class) {
            $output->writeln("<error>Class does not exist</error>");
            return Command::INVALID;
        }

        $requestSeeder = (new $namespace())->run();

        if ($requestSeeder->status === 'error') {
            $output->writeln("<error>{$requestSeeder->message}</error>");
            return Command::INVALID;
        }

        $output->writeln("<info>{$requestSeeder->message}</info>");
        return Command::SUCCESS;
    }

}