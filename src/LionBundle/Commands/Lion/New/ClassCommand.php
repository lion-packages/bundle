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
 * Create classes anywhere in the application
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class ClassCommand extends Command
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): ClassCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): ClassCommand
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
            ->setName('new:class')
            ->setDescription('Command needed to create classes')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name');
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
        /** @var string $class */
        $class = $input->getArgument('class');

        $this->classFactory->classFactory('app/', $class);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                /**
                 * Class Description
                 *
                 * @package {$namespace}
                 */
                class {$class}
                {
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  CLASS: {$class}"));

        $output->writeln($this->successOutput("\t>>  CLASS: the '{$namespace}\\{$class}' class has been generated"));

        return parent::SUCCESS;
    }
}
