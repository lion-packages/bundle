<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelectTableCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "db:select";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command to read the first 10 rows of a table")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('columns', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'What columns should you read?')
            ->addOption('rows', 'r', InputOption::VALUE_REQUIRED, 'Do you want to specify the number of rows?')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$entity = $input->getArgument("entity");
        $columns = $input->getOption("columns");
        $rows = $input->getOption("rows");
        $connection = $input->getOption("connection");

        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;

        // validate table
        $validate = DB::connection($main_conn)->show()
            ->tables()
            ->from($main_conn)
            ->like($entity)
            ->get();

        if (isset($validate->status)) {
            $output->writeln($this->errorOutput("\t>>  the '{$entity}' table or view does not exist"));
            return Command::INVALID;
        }

        // read columns table
        $columns_table = [];

        if (arr->of($columns)->length() === 0) {
            $columns_db = DB::connection($main_conn)->show()->columns()->from($entity)->getAll();

            if (isset($columns_db->status)) {
                $output->writeln($this->successOutput("\t>>  {$columns_db->message}"));
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
        $rows_table = DB::connection($main_conn)
            ->table($entity)
            ->select(...$columns_table)
            ->limit(0, $final_limit)
            ->fetchMode(\PDO::FETCH_ASSOC)
            ->getAll();

        if (isset($rows_table->status)) {
            $output->writeln($this->successOutput("\t>>  {$rows_table->message}"));
            return Command::SUCCESS;
        }

        if (!isset($rows_table[0])) {
            $rows_table = [$rows_table];
        }

        (new Table($output))
            ->setHeaderTitle("<info> TABLE " . str->of($entity)->upper()->get() . " </info>")
            ->setHeaders($columns_table)
            ->setRows(is_array($rows_table[0]) ? $rows_table : [$rows_table])
            ->render();

		return Command::SUCCESS;
	}
}
