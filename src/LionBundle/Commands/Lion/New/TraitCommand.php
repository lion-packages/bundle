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
 * Generate a trait
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class TraitCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): TraitCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): TraitCommand
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
            ->setName('new:trait')
            ->setDescription('Command required for trait creation')
            ->addArgument('trait', InputArgument::OPTIONAL, 'Trait name', 'ExampleTrait');
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
        $trait = $input->getArgument('trait');

        $this->classFactory->classFactory('app/Traits/', $trait);

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

                /**
                 * Trait Description 'ExampleTrait'
                 */
                trait {$class}
                {
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  TRAIT: {$class}"));

        $output->writeln($this->successOutput("\t>>  TRAIT: the '{$namespace}\\{$class}' trait has been generated"));

        return Command::SUCCESS;
    }
}
