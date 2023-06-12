<?php

namespace App\Console\Framework\DB;

use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $cont = 0;

        foreach ($connections['connections'] as $keyConnection => $connection) {
            $tables = DB::connection($keyConnection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();

            $output->write("\033[1;33m");
            $output->write("\t>>");
            $output->write("\033[0m");
            $output->writeln("  <comment>DATABASE: {$connection['dbname']}</comment>\n");

            foreach ($tables as $keyTable => $table) {
                $output->write("\033[1;33m");
                $output->write(">>>>");
                $output->write("\033[0m");
                $output->writeln("  TABLE: " . $table->{"Tables_in_{$connection['dbname']}"});

                $this->getApplication()->find('db:rules')->run(
                    new ArrayInput([
                        'entity' => $table->{"Tables_in_{$connection['dbname']}"},
                        '-c' => $keyConnection
                    ]),
                    $output
                );

                if ($keyTable < (arr->of($tables)->length() - 1)) {
                    $output->writeln("");
                }
            }

            if ($cont < (arr->of($connections['connections'])->length() - 1)) {
                $output->writeln("");
            }

            $cont++;
        }

		return Command::SUCCESS;
	}

}
