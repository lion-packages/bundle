<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the defined seeds
 *
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class DBSeedCommand extends Command
{
    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setStore(Store $store): DBSeedCommand
    {
        $this->store = $store;

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
            ->setDescription('Run the available seeds')
            ->addOption('run', '-r', InputOption::VALUE_OPTIONAL, 'Number of executions', 1);
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
        if (isError($this->store->exist('database/Seed/'))) {
            $output->writeln($this->errorOutput("\t>> SEED: there are no defined seeds"));

            return Command::FAILURE;
        }

        $end = (int) $input->getOption('run');

        /** @var array<int, SeedInterface> $files */
        $files = [];

        foreach ($this->store->view('./database/Seed/') as $seed) {
            if (isSuccess($this->store->validate([$seed], ['php']))) {
                $class = getNamespaceFromFile($seed, 'Database\\Seed\\', 'Seed/');

                /** @var SeedInterface $seedInterface */
                $seedInterface = new $class();

                $files[] = $seedInterface;
            }
        }

        foreach ($this->orderList($files) as $seedInterface) {
            $output->writeln($this->warningOutput("\t>>  SEED: " . $seedInterface::class));

            for ($i = 0; $i < $end; $i++) {
                $response = $seedInterface->run();

                if (isError($response)) {
                    $output->writeln($this->errorOutput("\t>>  SEED: {$response->message}"));
                } else {
                    $output->writeln($this->successOutput("\t>>  SEED: {$response->message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>>  SEED: seeds executed"));

        return Command::SUCCESS;
    }

    /**
     * Sorts the list of elements by the value defined in the INDEX constant
     *
     * @param  array $files [Class List]
     *
     * @return array<SeedInterface>
     */
    private function orderList(array $files): array
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
}
