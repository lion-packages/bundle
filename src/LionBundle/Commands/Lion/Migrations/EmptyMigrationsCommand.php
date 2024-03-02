<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmptyMigrationsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('migrate:empty')
            ->setDescription('Empties all tables built with the migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connections = (object) Schema::getConnections();

        foreach ($connections->connections as $connectionName => $connection) {
            $tables = DB::connection($connectionName)->show()->tables()->getAll();

            if (!is_array($tables) && isSuccess($tables)) {
                $output->writeln($this->warningOutput("\t>> MIGRATION: no tables available"));

                continue;
            }

            if (!is_array($tables) && isError($tables)) {
                $output->writeln($this->errorOutput("\t>> MIGRATION: {$tables->message}"));

                continue;
            }

            foreach ($tables as $table) {
                $response = Schema::connection($connectionName)
                    ->truncateTable($table->{"Tables_in_{$connection['dbname']}"})
                    ->execute();

                $output->writeln(
                    $this->warningOutput(
                        "\t>> MIGRATION: {$connection['dbname']}." . $table->{"Tables_in_{$connection['dbname']}"}
                    )
                );

                if (isError($response)) {
                    $output->writeln($this->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $output->writeln($this->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>> All tables have been truncated"));

        return Command::SUCCESS;
    }
}
