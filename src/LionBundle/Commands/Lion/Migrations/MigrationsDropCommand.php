<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes all defined schemas from the database.
 */
class MigrationsDropCommand extends Command
{
    /**
     * Manages the processes of creating or executing migrations.
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    #[Inject]
    public function setMigrations(Migrations $migrations): MigrationsDropCommand
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('migrate:drop')
            ->setDescription('Removes all defined schemas from the database.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes.
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return int
     *
     * @throws Exception If the file could not be opened.
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrations->resetDatabases();

        $output->writeln($this->successOutput("\t>> Drop the database schema."));

        return parent::SUCCESS;
    }
}
