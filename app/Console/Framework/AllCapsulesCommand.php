<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption, ArrayInput };
use Symfony\Component\Console\Output\OutputInterface;
use LionCommand\Functions\{ FILES, ClassPath };
use LionSQL\Drivers\MySQLDriver as Builder;

class AllCapsulesCommand extends Command {

    protected static $defaultName = "new:all-capsule";
    private string $default_path = "app/Class/";

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

        foreach ($all_tables as $keyTables => $tableDB) {
            $this->getApplication()->find('new:capsule')->run(
                new ArrayInput([
                    'capsule' => $tableDB->{"Tables_in_" . env->DB_NAME},
                    '--path' => ($path === null ? false : $path)
                ]),
                $output
            );
        }

        return Command::SUCCESS;
    }

}