<?php

namespace App\Console\Framework\DB;

use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelectTableCommand extends Command {

	protected static $defaultName = "db:select";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to read the first 10 rows of a table")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('columns', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'What columns should you read?')
            ->addOption('rows', 'r', InputOption::VALUE_REQUIRED, 'Do you want to specify the number of rows?')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$entity = $input->getArgument("entity");
        $columns = $input->getOption("columns");
        $rows = $input->getOption("rows");
        $connection = $input->getOption("connection");

        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;

        // read columns table
        $columns_table = [];

        if (arr->of($columns)->length() === 0) {
            $columns_db = DB::connection($main_conn)->show()->columns()->from($entity)->getAll();

            if (isset($columns_db->status)) {
                $output->writeln("<info>\t>>  {$columns_db->message}</info>");
                return Command::SUCCESS;
            }

            foreach ($columns_db as $key => $column) {
                $columns_table[] = $column->Field;
            }
        } else {
            $columns_table = $columns;
        }

        // read entity
        $final_limit = ($rows === null ? 10 : (int) $rows);
        $rows_table = DB::connection($main_conn)->table($entity)->select(...$columns_table)->limit(0, $final_limit)->fetchMode(\PDO::FETCH_ASSOC)->getAll();

        if (isset($rows_table->status)) {
            $output->writeln("<info>\t>>  {$rows_table->message}</info>");
            return Command::SUCCESS;
        }

        (new Table($output))
            ->setHeaderTitle("<info> TABLE " . str->of($entity)->upper()->get() . " </info>")
            ->setHeaders($columns_table)
            ->setRows($rows_table)
            ->render();

		return Command::SUCCESS;
	}

}
