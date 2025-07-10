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
 * Generate a factory to generate information
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class FactoryCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): FactoryCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): FactoryCommand
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
            ->setName('new:factory')
            ->setDescription('Command required for the creation of new factories')
            ->addArgument('factory', InputArgument::OPTIONAL, 'Factory name', 'ExampleFactory');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return int
     *
     * @throws Exception If the file could not be opened
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $factory */
        $factory = $input->getArgument('factory');

        $this->classFactory->classFactory('database/Factory/', $factory);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Bundle\Interface\FactoryInterface;

                /**
                 * Data factory for the entity ''
                 */
                class {$class} implements FactoryInterface
                {
                    /**
                     * {@inheritDoc}
                     */
                    public static function columns(): array
                    {
                        return [
                            'name',
                        ];
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public static function definition(): array
                    {
                        return [
                            [
                                fake()->name(),
                            ],
                        ];
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  FACTORY: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  FACTORY: The factory has been generated successfully."));

        return parent::SUCCESS;
    }
}
