<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

use DI\Attribute\Inject;
use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Files\Store;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages the processes of creating or executing migrations
 *
 * @property Store $store [Manipulate system files]
 * @property array $loadedMigrations [Stores already loaded migrations]
 *
 * @package Lion\Bundle\Helpers\Commands\Migrations
 */
class Migrations
{
    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Stores already loaded migrations]
     *
     * @var array<string, MigrationUpInterface>
     */
    private array $loadedMigrations = [];

    #[Inject]
    public function setStore(Store $store): Migrations
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Sorts the list of elements by the value defined in the INDEX constant
     *
     * @param array<string, MigrationUpInterface> $files [Class List]
     *
     * @return array<string, MigrationUpInterface>
     *
     * @internal
     */
    public function orderList(array $files): array
    {
        uasort($files, function ($classA, $classB) {
            $namespaceA = $classA::class;

            $namespaceB = $classB::class;

            if (!defined($namespaceA . "::INDEX")) {
                return -1;
            }

            if (!defined($namespaceB . "::INDEX")) {
                return -1;
            }

            return $classA::INDEX <=> $classB::INDEX;
        });

        return $files;
    }

    /**
     * Gets defined migrations categorized by type
     *
     * @return array<string, array<string, MigrationUpInterface>>
     *
     * @internal
     */
    public function getMigrations(): array
    {
        /** @var array<string, array<string, MigrationUpInterface>> $allMigrations */
        $allMigrations = [
            TableInterface::class => [],
            ViewInterface::class => [],
            StoreProcedureInterface::class => [],
        ];

        foreach ($this->store->getFiles('./database/Migrations/') as $migration) {
            if (isSuccess($this->store->validate([$migration], ['php']))) {
                $namespace = $this->store->getNamespaceFromFile($migration, 'Database\\Migrations\\', 'Migrations/');

                if (!isset($this->loadedMigrations[$migration])) {
                    $this->loadedMigrations[$migration] = new $namespace();
                }

                $tableMigration = $this->loadedMigrations[$migration];

                if ($tableMigration instanceof TableInterface) {
                    $allMigrations[TableInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof ViewInterface) {
                    $allMigrations[ViewInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof StoreProcedureInterface) {
                    $allMigrations[StoreProcedureInterface::class][$namespace] = $tableMigration;
                }
            }
        }

        return $allMigrations;
    }

    /**
     * Run the migrations
     *
     * @param Command $command [Extends the functions of the Command class to
     * format messages with different colors]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, MigrationUpInterface> $files [List of migration files]
     *
     * @return void
     *
     * @codeCoverageIgnore
     * @internal
     */
    public function executeMigrations(Command $command, OutputInterface $output, array $files): void
    {
        foreach ($files as $namespace => $classObject) {
            if ($classObject instanceof MigrationUpInterface) {
                $response = $classObject->up();

                $output->writeln($command->warningOutput("\t>> MIGRATION: {$namespace}"));

                if (isError($response)) {
                    $output->writeln($command->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $output->writeln($command->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }
    }
}
