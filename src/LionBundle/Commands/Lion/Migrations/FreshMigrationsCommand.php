<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreshMigrationsCommand extends Command
{
    private Container $container;
    private Store $store;

    /**
     * @required
     * */
    public function setContainer(Container $container): FreshMigrationsCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): FreshMigrationsCommand
    {
        $this->store = $store;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = [];

        foreach ($this->container->getFiles('./database/Migrations/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->container->getNamespace(
                    strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? str_replace('\\', '/', $file) : $file,
                    'Database\\Migrations\\',
                    'Migrations/'
                );

                $files[$namespace] = include_once($file);
            }
        }

        foreach ($this->orderList($files) as $namespace => $classObject) {
            if ($classObject instanceof MigrationUpInterface) {
                /** @var MigrationUpInterface $classObject */
                $response = $classObject->up();

                if (isError($response)) {
                    $output->writeln($this->warningOutput("\t>> MIGRATION: {$namespace}"));
                    $output->writeln($this->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $output->writeln($this->warningOutput("\t>> MIGRATION: {$namespace}"));
                    $output->writeln($this->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>> Migrations executed successfully"));

        return Command::SUCCESS;
    }

    private function orderList(array $files): array
    {
        uasort($files, function($classA, $classB) {
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
}
