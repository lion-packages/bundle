<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NewMigrateCommand extends Command {

	protected static $defaultName = "migrate:new";
    private array $options = ["TABLE", 'VIEW', 'PROCEDURE'];

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to generate a new migration")
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        // get migration name and validate that it is not in subfolders
        $migration = $input->getArgument('migration');
        if (str->of($migration)->test("/.*\//")) {
            $output->writeln("<error>Migration cannot be inside subfolders</error>");
            return Command::INVALID;
        }

        // select type of migration
        $option = $this->getHelper('question')->ask($input, $output,
            (new ChoiceQuestion("What type of migration do you want to create?", $this->options, 0))
                ->setErrorMessage('The selected option is not valid')
        );

        // select connection
        $connections = DB::getConnections();
        $connections = arr->of($connections['connections'])->keys()->get();
        $connection = $this->getHelper('question')->ask($input, $output,
            (new ChoiceQuestion("Which connection does the migration belong to?", $connections, 0))
                ->setErrorMessage('The selected option is not valid')
        );

        $db_pascal = str->of($connection)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
        $migration_pascal = str->of($migration)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
        $migration_pascal = str->of($option === "TABLE" ? "Table" : ($option === "VIEW" ? "View" : "Procedure"))->concat($migration_pascal)->get();
        $env_var = array_search($connection, (array) env);
        Store::folder("database/Migrations/{$db_pascal}/");
		ClassPath::new("database/Migrations/{$db_pascal}/{$migration_pascal}", "php");

        if ($option === "TABLE") {
            ClassPath::add(str->of(ClassPath::getTemplateCreateTable())->replace("env->DB_NAME", "env->{$env_var}")->get());
        } elseif ($option === "VIEW") {
            ClassPath::add(str->of(ClassPath::getTemplateCreateView())->replace("env->DB_NAME", "env->{$env_var}")->get());
        } elseif ($option === "PROCEDURE") {
            ClassPath::add(str->of(ClassPath::getTemplateCreateProcedure())->replace("env->DB_NAME", "env->{$env_var}")->get());
        }

        $output->write("\033[1;33m");
        $output->write("\t>>");
        $output->write("\033[0m");
        $output->writeln("  {$option}: <info>Migration 'database/Migrations/{$db_pascal}/{$migration_pascal}' has been generated</info>");

        ClassPath::force();
        ClassPath::close();

		return Command::SUCCESS;
	}

}
