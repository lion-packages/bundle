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

class SelectColumnsCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "db:columns";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command to read the columns of an entity")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument("entity");
        $connection = $input->getOption("connection");

        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;

        // validate table
        $validate = DB::connection($main_conn)
            ->show()
            ->tables()
            ->from($main_conn)
            ->like($entity)
            ->get();

        if (isset($validate->status)) {
            $output->writeln($this->errorOutput("\t>>  the '{$entity}' table or view does not exist"));
            return Command::INVALID;
        }

        $columns_db = DB::connection($main_conn)
            ->show()
            ->columns()
            ->from($entity)
            ->fetchMode(\PDO::FETCH_ASSOC)
            ->getAll();

        if (isset($columns_db->status)) {
            $output->writeln($this->successOutput("\t>>  {$columns_db->message}"));
            return Command::SUCCESS;
        }

        if (!isset($columns_db[0])) {
            $columns_db = [$columns_db];
        }

        (new Table($output))
            ->setHeaderTitle("<info> TABLE " . str->of($entity)->upper()->get() . " </info>")
            ->setHeaders(["FIELD", "TYPE", "NULL", "KEY", "DEFAULT", "EXTRA"])
            ->setRows($columns_db)
            ->render();

		return Command::SUCCESS;
	}
}
