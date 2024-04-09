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
 * Generate an Service
 *
 * @property ClassFactory $classFactory [Fabricates the data provided to
 * manipulate information (folder, class, namespace)]
 * @property Store $store [Store class object]
 *
 * @package App\Console\Commands
 */
class ServiceCommand extends Command
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
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
     */
    public function setClassFactory(ClassFactory $classFactory): ServiceCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): ServiceCommand
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
            ->setName('new:service')
            ->setDescription('Command required for creating new Services')
            ->addArgument('service', InputArgument::OPTIONAL, 'Service name', 'ExampleService');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
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
        $service = $input->getArgument('service');

        $this->classFactory->classFactory('app/Http/Services/', $service);

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $folder = $this->classFactory->getFolder();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                /**
                 * Service '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class}
                {
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SERVICE: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  SERVICE: the '{$namespace}\\{$class}' service has been generated")
        );

        return Command::SUCCESS;
    }
}
