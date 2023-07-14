<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ConsoleOutput;
use Carbon\Carbon;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDatabaseCommand extends Command {

	use ConsoleOutput;

	protected static $defaultName = "db:export";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to export copies of databases established in the config")
            ->addArgument('connection', InputArgument::REQUIRED, 'Do you want to use a specific connection?');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $connections = DB::getConnections();
        $info = $connections['connections'][$input->getArgument("connection")];
        $actual_date = Carbon::now()->format('Y_m_d');

        if ($info['type'] === "mysql") {
            $path = __DIR__ . "/../../../../storage/backups/database/";
            $file_name = "{$info['dbname']}_{$actual_date}.sql";
            kernel->execute("MYSQL_PWD={$info['password']} mysqldump -h {$info['host']} -u {$info['user']} -p --routines --triggers --events {$info['dbname']} > {$path}{$file_name}");
            $output->writeln($this->successOutput("DATABASE: {$info['dbname']}"));
            $output->writeln($this->warningOutput("DATABASE: exported database in ./storage/backups/database/{$info['dbname']}_{$actual_date}.sql"));
        }

		return Command::SUCCESS;
	}

}
