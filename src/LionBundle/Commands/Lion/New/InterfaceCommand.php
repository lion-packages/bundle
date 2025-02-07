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
 * Generate an interface
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class InterfaceCommand extends Command
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

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): InterfaceCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): InterfaceCommand
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
            ->setName('new:interface')
            ->setDescription('Command required for interface creation')
            ->addArgument('interface', InputArgument::OPTIONAL, 'Interface name', 'ExampleInterface');
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
        /** @var string $interface */
        $interface = $input->getArgument('interface');

        $this->classFactory->classFactory('app/Interfaces/', $interface);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                /**
                 * Description of the '{$class}' interface
                 *
                 * @package {$namespace}
                 */
                interface {$class}
                {
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  INTERFACE: {$class}"));

        $output->writeln(
            $this->successOutput(
                "\t>>  INTERFACE: the '{$namespace}\\{$class}' interface has been generated"
            )
        );

        return parent::SUCCESS;
    }
}
