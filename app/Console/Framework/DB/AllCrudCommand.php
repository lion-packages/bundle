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

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("command to generate all the controllers and models of the entities with their respective CRUD functions");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$connections = DB::getConnections();
        $cont = 0;

        foreach ($connections['connections'] as $key => $conn) {
            $output->writeln("<question>\t>>  {$conn['dbname']}</question>");
            $tables = DB::connection($conn['dbname'])->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();

            foreach ($tables as $key => $table) {
                $values = arr->of((array) $table)->values()->get();

                if (in_array(strtolower($values[0]), ["groups", "group", "select"])) {
                    $output->writeln("\n<error>\t>>  Omitted entity CRUD '{$conn['dbname']}.{$values[0]}', contains reserved names</error>\n");
                } else {
                    $this->getApplication()->find('db:crud')->run(
                        new ArrayInput([
                            'entity' => $values[0],
                            '-c' => $conn['dbname']
                        ]),
                        $output
                    );
                }
            }

            if ($cont < (count($connections['connections']) - 1)) {
                $output->writeln("");
            }

            $cont++;
        }

		return Command::SUCCESS;
	}

}
