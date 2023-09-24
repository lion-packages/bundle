<?php

namespace App\Console\Framework\DB;

use LionHelpers\Arr;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowDatabasesCommand extends Command
{
	protected static $defaultName = "db:show";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command required to display available database connections");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connections = DB::getConnections();
        $size = Arr::of($connections['connections'])->length();
        $list_connections = [];

        foreach ($connections['connections'] as $key => $connection) {
            $item = [
                'type' => "<fg=#FFB63E>{$connection['type']}</>",
                'host' => "<fg=#FFB63E>{$connection['host']}</>",
                'port' => "<fg=#FFB63E>{$connection['port']}</>",
                'dbname' => "",
                'user' => $connection['user']
            ];

            if ($connection['dbname'] === $connections['default']) {
                $item['dbname'] = "{$connection['dbname']} <fg=#FFB63E>(default)</>";
            } else {
                $item['dbname'] = $connection['dbname'];
            }

            $list_connections[] = $item;
        }

        (new Table($output))
            ->setHeaderTitle('<info> DATABASE CONNECTIONS </info>')
            ->setHeaders(['DATABASE CONNECTION', 'DATABASE HOST', 'DATABASE PORT', 'DATABASE NAME', 'DATABASE USER'])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [" . $size . "] connections </info>"
                    : ($size === 1
                        ? "<info> showing a single connection </info>"
                        : "<info> No connections available </info>"
                    )
            )
            ->setRows($list_connections)
            ->render();

		return Command::SUCCESS;
	}
}
