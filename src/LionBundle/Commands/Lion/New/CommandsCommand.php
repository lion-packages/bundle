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
 * Generate a Command class to execute commands
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CommandsCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): CommandsCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): CommandsCommand
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
            ->setName('new:command')
            ->setDescription('Command required for the creation of new Commands')
            ->addArgument('new-command', InputArgument::OPTIONAL, 'Command name', 'ExampleCommand');
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
        $command = $input->getArgument('new-command');

        $this->classFactory->classFactory('app/Console/Commands/', $command);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Command\Command;
                use Symfony\Component\Console\Input\InputInterface;
                use Symfony\Component\Console\Output\OutputInterface;

                /**
                 * {$class} description
                 *
                 * @package {$namespace}
                 */
                class {$class} extends Command
                {
                    /**
                     * Configures the current command
                     *
                     * @return void
                     */
                    protected function configure(): void
                    {
                        \$this
                            ->setName('example')
                            ->setDescription('');
                    }

                    /**
                     * Initializes the command after the input has been bound and before the
                     * input is validated
                     *
                     * This is mainly useful when a lot of commands extends one main command
                     * where some things need to be initialized based on the input arguments and
                     * options
                     *
                     * @param InputInterface \$input [InputInterface is the interface implemented
                     * by all input classes]
                     * @param OutputInterface \$output [OutputInterface is the interface
                     * implemented by all Output classes]
                     *
                     * @see InputInterface::bind()
                     * @see InputInterface::validate()
                     *
                     * @return void
                     */
                    protected function initialize(InputInterface \$input, OutputInterface \$output): void
                    {
                    }

                    /**
                     * Interacts with the user
                     *
                     * This method is executed before the InputDefinition is validated
                     *
                     * This means that this is the only place where the command can
                     * interactively ask for values of missing required arguments
                     *
                     * @param InputInterface \$input [InputInterface is the interface implemented
                     * by all input classes]
                     * @param OutputInterface \$output [OutputInterface is the interface
                     * implemented by all Output classes]
                     *
                     * @return void
                     */
                    protected function interact(InputInterface \$input, OutputInterface \$output): void
                    {
                    }

                    /**
                     * Executes the current command.
                     *
                     * This method is not abstract because you can use this class
                     * as a concrete class. In this case, instead of defining the
                     * execute() method, you set the code to execute by passing
                     * a Closure to the setCode() method.
                     *
                     * @param InputInterface \$input [InputInterface is the interface implemented
                     * by all input classes]
                     * @param OutputInterface \$output [OutputInterface is the interface
                     * implemented by all Output classes]
                     *
                     * @return int 0 if everything went fine, or an exit code
                     *
                     * @throws LogicException When this abstract method is not implemented
                     *
                     * @see setCode()
                     */
                    protected function execute(InputInterface \$input, OutputInterface \$output): int
                    {
                        \$output->writeln('ExampleCommand');

                        return Command::SUCCESS;
                    }
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  COMMAND: {$class}"));

        $output->writeln($this->successOutput("\t>>  COMMAND: the '{$namespace}\\{$class}' command has been generated"));

        return Command::SUCCESS;
	}
}
