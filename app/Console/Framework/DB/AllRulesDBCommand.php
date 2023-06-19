<?php

namespace App\Console\Framework\DB;

use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllRulesDBCommand extends Command {

	protected static $defaultName = "db:all-rules";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to generate all rules for all entities");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $connections = DB::getConnections();

        foreach ($connections['connections'] as $keyConnection => $connection) {
            $tables = DB::connection($keyConnection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();
            $output->writeln("<question>\t>>  DATABASE: {$connection['dbname']}</question>");

            foreach ($tables as $keyTable => $table) {
                $output->writeln("<question>\t>>  TABLE: " . $table->{"Tables_in_{$connection['dbname']}"} . "</question>");

                $this->getApplication()->find('db:rules')->run(
                    new ArrayInput([
                        'entity' => $table->{"Tables_in_{$connection['dbname']}"},
                        '-c' => $keyConnection
                    ]),
                    $output
                );
            }
        }

		return Command::SUCCESS;
	}

}
