<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Command\Command;
use Lion\Database\Connection;
use LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Gets a list of all available database connections
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class ShowDatabasesCommand extends Command
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('db:show')
            ->setDescription('Command required to display available database connections');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connections = Connection::getConnections();

        $size = count($connections);

        $listConnections = [];

        foreach ($connections as $connectionName => $connection) {
            $listConnections[] = [
                'connectionName' => $this->infoOutput($connectionName),
                'type' => "<fg=#FFB63E>{$connection['type']}</>",
                'host' => $connection['host'] ?? '',
                'port' => $connection['port'] ?? '',
                /** @phpstan-ignore-next-line */
                'dbname' => $connection['dbname'] ?? '',
                'user' => $connection['user'] ?? '',
            ];
        }

        new Table($output)
            ->setHeaderTitle('<info> DATABASE CONNECTIONS </info>')
            ->setHeaders([
                'CONNECTION NAME',
                'DRIVER CONNECTION',
                'DATABASE HOST',
                'DATABASE PORT',
                'DATABASE NAME',
                'DATABASE USER',
            ])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [{$size}] connections </info>"
                    : (
                        $size === 1
                        ? '<info> showing a single connection </info>'
                        : '<info> No connections available </info>'
                    )
            )
            ->setRows($listConnections)
            ->render();

        return parent::SUCCESS;
    }
}
