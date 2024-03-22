<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the defined seeds
 *
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class DBSeedCommand extends Command
{
    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * @required
     * */
    public function setContainer(Container $container): DBSeedCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     * */
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./database/Seed/'))) {
            $output->writeln($this->errorOutput("\t>> SEED: there are no defined seeds"));

            return Command::FAILURE;
        }

        $end = (int) $input->getOption('run');

        /** @var array<SeedInterface> $files */
        $files = [];

        foreach ($this->container->getFiles($this->container->normalizePath('./database/Seed/')) as $seed) {
            if (isSuccess($this->store->validate([$seed], ['php']))) {
                $class = $this->container->getNamespace(
                    $seed,
                    'Database\\Seed\\',
                    $this->container->normalizePath('Seed/')
                );

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
