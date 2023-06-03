<?php

namespace App\Console\Framework\DB;

use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllCrudCommand extends Command {

	protected static $defaultName = "db:all-crud";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating all CRUD...</comment>\n");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("command to generate all the controllers and models of the entities with their respective CRUD functions");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$connections = DB::getConnections();

        foreach ($connections['connections'] as $key => $conn) {
            $tables = DB::connection($conn['dbname'])
                ->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'")
                ->getAll();

            foreach ($tables as $key => $table) {
                $values = arr->of((array) $table)->values()->get();

                $this->getApplication()->find('db:crud')->run(
                    new ArrayInput([
                        'entity' => $values[0],
                        '--path' => $conn['dbname'] . "/",
                        '--connection' => $conn['dbname']
                    ]),
                    $output
                );
            }
        }

		return Command::SUCCESS;
	}

}
