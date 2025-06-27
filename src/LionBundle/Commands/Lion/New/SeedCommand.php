<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a seed
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class SeedCommand extends Command
{
    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace)
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * Manipulate system files
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): SeedCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): SeedCommand
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
            ->setName('new:seed')
            ->setDescription('Command required for creating new seeds')
            ->addArgument('seed', InputArgument::OPTIONAL, 'Name seed', 'ExampleSeed');
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
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $seed */
        $seed = $input->getArgument('seed');

        $this->classFactory->classFactory('database/Seed/', $seed);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Bundle\Interface\SeedInterface;
                use stdClass;

                /**
                 * Description of '{$class}' Seed
                 */
                class {$class} implements SeedInterface
                {
                    /**
                     * Index number for seed execution priority
                     *
                     * @const INDEX
                     */
                    public const ?int INDEX = null;

                    /**
                     * {@inheritDoc}
                     */
                    public function run(): int|stdClass
                    {
                        return success('OK');
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SEED: {$class}"));

        $output->writeln($this->successOutput("\t>>  SEED: the '{$namespace}\\{$class}' seed has been generated"));

        return parent::SUCCESS;
    }
}
