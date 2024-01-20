<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\DB\MySQL;

use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use PDO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class DBCapsulesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('db:mysql:capsules')
            ->setDescription('Command required for the creation of all new Capsules available from the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionsKeys = array_keys(DB::getConnections()['connections']);
        $listAllTables = [];

        foreach ($connectionsKeys as $connection) {
            $output->writeln($this->infoOutput("\t>>  CONNECTION: {$connection}"));

            $allTables = DB::connection($connection)
                ->show()
                ->tables()
                ->from($connection)
                ->fetchMode(PDO::FETCH_ASSOC)
                ->getAll();

            if (!isset($allTables->status)) {
                $listAllTables[] = ['connection' => $connection, 'all-tables' => $allTables];
            } else {
                $output->writeln($this->successOutput("\t>>  CONNECTION: {$allTables->message}"));
            }
        }

        foreach ($listAllTables as $table) {
            foreach ($table['all-tables'] as $tableDB) {
                $values = array_values($tableDB);

                $this->getApplication()
                    ->find('db:mysql:capsule')
                    ->run(new ArrayInput(['entity' => reset($values), '--connection' => $table['connection']]), $output);
            }
        }

        return Command::SUCCESS;
    }
}
