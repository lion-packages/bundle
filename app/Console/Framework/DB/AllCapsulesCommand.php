<?php

namespace App\Console\Framework\DB;

use LionHelpers\Arr;
use LionHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption, ArrayInput };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use LionSQL\Drivers\MySQL\MySQL as DB;

class AllCapsulesCommand extends Command {

    protected static $defaultName = "db:all-capsules";

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription('Command required for the creation of all new Capsules available from the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $connections = DB::getConnections();
        $connections_keys = array_keys($connections['connections']);
        $list_all_tables = [];

        foreach ($connections_keys as $key => $connection) {
            $output->writeln("<comment>\t>>  CONNECTION: {$connection}</comment>");
            $all_tables = DB::connection($connection)->show()->tables()->from($connection)->getAll();

            if (!isset($all_tables->status)) {
                $list_all_tables[] = [
                    'connection' => $connection,
                    'all-tables' => $all_tables,
                    'size' => Arr::of($all_tables)->length()
                ];
            } else {
                $output->writeln("<info>\t>>  CONNECTION: {$all_tables->message}</info>");
            }
        }

        foreach ($list_all_tables as $key => $table) {
            $progressBar = new ProgressBar($output, $table['size']);
            $progressBar->setFormat('debug_nomax');
            $progressBar->start();

            foreach ($table['all-tables'] as $keyTables => $tableDB) {
                $tableDB = (array) $tableDB;
                $table_key = array_keys($tableDB);

                if ($keyTables === ($table['size'] - 1)) {
                    $progressBar->setBarCharacter('<info>=</info>');
                } else {
                    $progressBar->setBarCharacter('<comment>=</comment>');
                }

                $path = Str::of($table['connection'])
                    ->replace("_", " ")
                    ->headline()
                    ->replace(" ", "")
                    ->get();

                $this->getApplication()->find('db:capsule')->run(
                    new ArrayInput([
                        'entity' => $tableDB[$table_key[0]],
                        '--path' => $path . "/",
                        '--connection' => $table['connection'],
                        '--message' => false
                    ]),
                    $output
                );

                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln("\n");
        }

        return Command::SUCCESS;
    }

}
