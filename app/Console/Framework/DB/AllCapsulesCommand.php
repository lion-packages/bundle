<?php

namespace App\Console\Framework\DB;

use LionHelpers\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption, ArrayInput };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use LionSQL\Drivers\MySQL as DB;

class AllCapsulesCommand extends Command {

    protected static $defaultName = "db:all-capsules";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating all the capsules...</comment>\n");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of all new Capsules available from the database'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $connections = DB::getConnections();
        $connections_keys = array_keys($connections['connections']);
        $list_all_tables = [];

        foreach ($connections_keys as $key => $connection) {
            $all_tables = DB::connection($connection)->show()->tables()->getAll();

            $list_all_tables[] = [
                'connection' => $connection,
                'all-tables' => $all_tables,
                'size' => Arr::of($all_tables)->length()
            ];
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

                $this->getApplication()->find('db:capsule')->run(
                    new ArrayInput([
                        'capsule' => $tableDB[$table_key[0]],
                        '--path' => $table['connection'] . "/",
                        '--connection' => $table['connection'],
                        '--message' => false
                    ]),
                    $output
                );

                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln("");
        }

        $output->writeln("");
        if (Arr::of($connections_keys)->length() > 1) {
            $join = Arr::of($connections_keys)->join(", ", " and ");
            $output->writeln("<info>Capsules for connections '{$join}' were generated successfully...</info>");
        } else {
            $output->writeln("<info>Capsules of the '{$connections_keys[0]}' connection were generated correctly...</info>");
        }

        return Command::SUCCESS;
    }

}