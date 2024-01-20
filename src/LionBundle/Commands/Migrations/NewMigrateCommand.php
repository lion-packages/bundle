<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Migrations;

use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewMigrateCommand extends Command
{
    private Store $store;

    /**
     * @required
     * */
    public function setStore(Store $store): NewMigrateCommand
    {
        $this->store = $store;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('migrate:new')
            ->setDescription("Command to generate a new migration")
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name')
            ->addArgument('connection', InputArgument::REQUIRED, 'Connection name')
            ->addOption('driver', 'd', InputOption::VALUE_OPTIONAL, 'Migration driver', 'mysql')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of migration', 'table');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // get migration name and validate that it is not in subfolders
        $migration = $input->getArgument('migration');

        if (str->of($migration)->test("/.*\//")) {
            $output->writeln($this->errorOutput("\t>>  migration cannot be inside subfolders"));

            return Command::INVALID;
        }

        // select type of migration
        // $option = str->of($input->getOption('type'))->lower()->get();

        // select connection
        // $connections = DB::getConnections();
        // $connections = arr->of($connections['connections'])->keys()->get();
        // $connection = $input->getArgument('connection');

        // $db_pascal = str->of($connection)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
        // $migration_pascal = str->of($migration)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
        // $migration_pascal = str->of($option === "table" ? "Table" : ($option === "view" ? "View" : "Procedure"))->concat($migration_pascal)->get();
        // $env_var = array_search($connection, (array) env);

        // if ($option === "table") {
        //     Store::folder("database/Migrations/{$db_pascal}/Tables/");
        //     $this->new("database/Migrations/{$db_pascal}/Tables/{$migration_pascal}", "php");
        //     $this->add(str->of($this->getTemplateCreateTable())->replace("env->DB_NAME", "env->{$env_var}")->get());
        //     $output->writeln($this->successOutput("\t>>  TABLE: migration 'database/Migrations/{$db_pascal}/Tables/{$migration_pascal}' has been generated"));
        // } elseif ($option === "view") {
        //     Store::folder("database/Migrations/{$db_pascal}/Views/");
        //     $this->new("database/Migrations/{$db_pascal}/Views/{$migration_pascal}", "php");
        //     $this->add(str->of($this->getTemplateCreateView())->replace("env->DB_NAME", "env->{$env_var}")->get());
        //     $output->writeln($this->successOutput("\t>>  VIEW: migration 'database/Migrations/{$db_pascal}/Views/{$migration_pascal}' has been generated"));
        // } elseif ($option === "procedure") {
        //     Store::folder("database/Migrations/{$db_pascal}/Procedures/");
        //     $this->new("database/Migrations/{$db_pascal}/Procedures/{$migration_pascal}", "php");
        //     $this->add(str->of($this->getTemplateCreateProcedure())->replace("env->DB_NAME", "env->{$env_var}")->get());
        //     $output->writeln($this->successOutput("\t>>  PROCEDURE: migration 'database/Migrations/{$db_pascal}/Procedures/{$migration_pascal}' has been generated"));
        // } else {
        //     $output->writeln($this->errorOutput("\t>>  the selected migration type does not exist"));
        //     return Command::INVALID;
        // }

        // $this->force();
        // $this->close();
		return Command::SUCCESS;
	}
}
