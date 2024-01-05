<?php

declare(strict_types=1);

namespace LionBundle\Commands\DB;

use LionCommand\Command;
use LionHelpers\Arr;
use LionDatabase\Drivers\MySQL as DB;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowDatabasesCommand extends Command
{
    private Arr $arr;

    /**
     * @required
     * */
    public function setArr(Arr $arr): ShowDatabasesCommand
    {
        $this->arr = $arr;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('db:show')
            ->setDescription('Command required to display available database connections');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connections = DB::getConnections();
        $size = $this->arr->of($connections['connections'])->length();
        $listConnections = [];

        foreach ($connections['connections'] as $connection) {
            $item = [
                'type' => "<fg=#FFB63E>{$connection['type']}</>",
                'host' => $connection['host'],
                'port' => $connection['port'],
                'dbname' => '',
                'user' => $connection['user']
            ];

            if ($connection['dbname'] === $connections['default']) {
                $item['dbname'] = "{$connection['dbname']} <fg=#FFB63E>(default)</>";
            } else {
                $item['dbname'] = $connection['dbname'];
            }

            $listConnections[] = $item;
        }

        (new Table($output))
            ->setHeaderTitle('<info> DATABASE CONNECTIONS </info>')
            ->setHeaders(['DATABASE CONNECTION', 'DATABASE HOST', 'DATABASE PORT', 'DATABASE NAME', 'DATABASE USER'])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [" . $size . "] connections </info>"
                    : ($size === 1
                        ? '<info> showing a single connection </info>'
                        : '<info> No connections available </info>'
                    )
            )
            ->setRows($listConnections)
            ->render();

		return Command::SUCCESS;
	}
}
