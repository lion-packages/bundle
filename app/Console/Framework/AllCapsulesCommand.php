<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption, ArrayInput };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use LionSQL\Drivers\MySQLDriver as Builder;

class AllCapsulesCommand extends Command {

    protected static $defaultName = "db:all-capsules";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating all the capsules...</comment>");

        Builder::init([
            'host' => env->DB_HOST,
            'port' => env->DB_PORT,
            'db_name' => env->DB_NAME,
            'user' => env->DB_USER,
            'password' => env->DB_PASSWORD
        ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of all new Capsules available from the database'
        )->addOption(
            'path', null, InputOption::VALUE_REQUIRED, 'Do you want to configure your own route?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getOption('path');
        $all_tables = Builder::showTables(env->DB_NAME);
        $size = count($all_tables);
        $progressBar = new ProgressBar($output, $size);
        $progressBar->setFormat('debug_nomax');
        $progressBar->start();

        foreach ($all_tables as $keyTables => $tableDB) {
            if ($keyTables < ($size * 0.90)) {
                $progressBar->setBarCharacter('<comment>=</comment>');
            } else {
                $progressBar->setBarCharacter('<info>=</info>');
            }

            $this->getApplication()->find('database:capsule')->run(
                new ArrayInput([
                    'capsule' => $tableDB->{"Tables_in_" . env->DB_NAME},
                    '--path' => ($path === null ? false : $path),
                    '--message' => false
                ]),
                $output
            );

            $progressBar->advance();
        }

        $progressBar->finish();
        return Command::SUCCESS;
    }

}