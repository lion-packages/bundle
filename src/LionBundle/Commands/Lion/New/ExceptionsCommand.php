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
 * Generate Exception classes to handle exceptions
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class ExceptionsCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): ExceptionsCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): ExceptionsCommand
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
            ->setName('new:exception')
            ->setDescription('Command required to create an exception')
            ->addArgument('exception', InputArgument::OPTIONAL, 'Exception name', 'ExampleException');
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
        /** @var string $exception */
        $exception = $input->getArgument('exception');

        $this->classFactory->classFactory('app/Exceptions/', $exception);

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

                use Lion\Exceptions\Exception;
                use Lion\Exceptions\Interfaces\ExceptionInterface;
                use Lion\Exceptions\Traits\ExceptionTrait;

                /**
                 * Description of '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class} extends Exception implements ExceptionInterface
                {
                    use ExceptionTrait;
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  EXCEPTION: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  EXCEPTION: the '{$namespace}\\{$class}' exception has been generated")
        );

        return parent::SUCCESS;
    }
}
