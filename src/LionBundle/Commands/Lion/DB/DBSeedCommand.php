<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the defined seeds
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class DBSeedCommand extends Command
{
    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Manages the processes of creating or executing migrations]
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    #[Inject]
    public function setStore(Store $store): DBSeedCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setMigrations(Migrations $migrations): DBSeedCommand
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('db:seed')
            ->setDescription('Run the available seeds');
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
     *
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('database/Seed/'))) {
            $output->writeln($this->errorOutput("\t>> SEED: there are no defined seeds"));

            return parent::FAILURE;
        }

        /** @var array<int, SeedInterface> $files */
        $files = [];

        foreach ($this->store->getFiles('./database/Seed/') as $seed) {
            if (isSuccess($this->store->validate([$seed], ['php']))) {
                $class = $this->store->getNamespaceFromFile($seed, 'Database\\Seed\\', 'Seed/');

                /** @var SeedInterface $seedInterface */
                $seedInterface = new $class();

                $files[$class] = $seedInterface;
            }
        }

        /** @phpstan-ignore-next-line */
        foreach ($this->migrations->orderList($files) as $seedInterface) {
            $output->writeln($this->warningOutput("\t>>  SEED: " . $seedInterface::class));

            if ($seedInterface instanceof SeedInterface) {
                $response = $seedInterface->run();

                /** @var string $message */
                $message = $response->message;

                if (isError($response)) {
                    $output->writeln($this->errorOutput("\t>>  SEED: {$message}"));
                } else {
                    $output->writeln($this->successOutput("\t>>  SEED: {$message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>>  SEED: seeds executed"));

        return parent::SUCCESS;
    }
}
