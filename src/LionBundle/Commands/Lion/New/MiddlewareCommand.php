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
 * Generate Middleware to create filters
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class MiddlewareCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): MiddlewareCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): MiddlewareCommand
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
            ->setName('new:middleware')
            ->setDescription('Command required for the creation of new Middleware')
            ->addArgument('middleware', InputArgument::OPTIONAL, 'Middleware name', 'ExampleMiddleware');
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
        /** @var string $middleware */
        $middleware = $input->getArgument('middleware');

        $this->classFactory->classFactory('app/Http/Middleware/', $middleware);

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

                use Lion\Bundle\Exceptions\MiddlewareException;
                use Lion\Request\Http;
                use Lion\Request\Status;

                /**
                 * Description of Middleware '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class}
                {
                    /**
                     * Middleware description
                     *
                     * @return void
                     *
                     * @throws MiddlewareException
                     */
                    public function example(): void
                    {
                        throw new MiddlewareException('message', Status::ERROR, Http::UNAUTHORIZED);
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  MIDDLEWARE: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  MIDDLEWARE: the '{$namespace}\\{$class}' middleware has been generated")
        );

        return parent::SUCCESS;
    }
}
