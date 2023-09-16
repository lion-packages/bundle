<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionHelpers\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class AllCapsulesCommand extends Command
{
    use ConsoleOutput;

    protected static $defaultName = "db:all-capsules";

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required for the creation of all new Capsules available from the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connections = DB::getConnections();
        $connections_keys = array_keys($connections['connections']);
        $list_all_tables = [];

        foreach ($connections_keys as $key => $connection) {
            $output->writeln($this->infoOutput("\t>>  CONNECTION: {$connection}"));
            $all_tables = DB::connection($connection)->show()->tables()->from($connection)->getAll();

            if (!isset($all_tables->status)) {
                $list_all_tables[] = [
                    'connection' => $connection,
                    'all-tables' => $all_tables,
                    'size' => Arr::of($all_tables)->length()
                ];
            } else {
                $output->writeln($this->successOutput("\t>>  CONNECTION: {$all_tables->message}"));
            }
        }

        foreach ($list_all_tables as $key => $table) {
            foreach ($table['all-tables'] as $keyTables => $tableDB) {
                $tableDB = (array) $tableDB;
                $table_key = array_keys($tableDB);

                $this->getApplication()->find('db:capsule')->run(
                    new ArrayInput([
                        'entity' => $tableDB[$table_key[0]],
                        '--connection' => $table['connection']
                    ]),
                    $output
                );
            }
        }

        return Command::SUCCESS;
    }
}
