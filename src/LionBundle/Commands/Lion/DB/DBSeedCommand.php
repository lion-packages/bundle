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

class DBSeedCommand extends Command
{
    private Container $container;
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

    protected function configure(): void
    {
        $this
            ->setName('db:seed')
            ->setDescription('Run the available seeds')
            ->addOption('run', '-r', InputOption::VALUE_OPTIONAL, 'Number of executions', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $end = (int) $input->getOption('run');

        foreach ($this->container->getFiles($this->container->normalizePath('./database/Seed/')) as $seed) {
            if (isSuccess($this->store->validate([$seed], ['php']))) {
                $class = $this->container->getNamespace(
                    $seed,
                    'Database\\Seed\\',
                    $this->container->normalizePath('Seed/')
                );

                /** @var SeedInterface $seedInterface */
                $seedInterface = new $class();

                for ($i = 0; $i < $end; $i++) {
                    $response = $seedInterface->run();

                    $output->writeln($this->warningOutput("\t>>  SEED: " . $seedInterface::class));

                    if (isError($response)) {
                        $output->writeln($this->errorOutput("\t>>  SEED: {$response->message}"));
                    } else {
                        $output->writeln($this->successOutput("\t>>  SEED: {$response->message}"));
                    }
                }
            }
        }

        $output->writeln($this->purpleOutput("\n\t>>  SEED: seeds executed"));

        return Command::SUCCESS;
    }
}
