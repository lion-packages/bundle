<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllRulesDBCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "db:all-rules";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command to generate all rules for all entities");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connections = DB::getConnections();

        foreach ($connections['connections'] as $keyConnection => $connection) {
            $tables = DB::connection($keyConnection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();
            $output->writeln($this->infoOutput("\t>>  DATABASE: {$connection['dbname']}"));

            foreach ($tables as $keyTable => $table) {
                $output->writeln($this->infoOutput("\t>>  TABLE: " . $table->{"Tables_in_{$connection['dbname']}"}));

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
