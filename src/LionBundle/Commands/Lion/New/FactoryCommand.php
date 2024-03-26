<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a factory to generate information
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class FactoryCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): FactoryCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
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
        $factory = $input->getArgument('factory');

        $this->classFactory->classFactory('database/Factory/', $factory);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Bundle\Interface\FactoryInterface;

                /**
                 * Description of the factory '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class} implements FactoryInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public static function columns(): array
                    {
                        return [
                            // ...
                        ];
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public static function definition(): array
                    {
                        return [
                            fake()->name()
                        ];
                    }
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  FACTORY: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  FACTORY: the '{$namespace}\\{$class}' factory has been generated")
        );

        return Command::SUCCESS;
    }
}
