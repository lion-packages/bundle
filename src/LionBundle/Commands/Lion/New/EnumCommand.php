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
 * Generate an Enum
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class EnumCommand extends Command
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
    public function setClassFactory(ClassFactory $classFactory): EnumCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): EnumCommand
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
            ->setName('new:enum')
            ->setDescription('Command required for creating new Enums')
            ->addArgument('enum', InputArgument::OPTIONAL, 'Enum name', 'ExampleEnum');
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
        /** @var string $enum */
        $enum = $input->getArgument('enum');

        $this->classFactory->classFactory('app/Enums/', $enum);

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

                /**
                 * Description of Enum '{$class}'
                 */
                enum {$class}: string
                {
                    /**
                     * Description of the case EXAMPLE
                     */
                    case EXAMPLE = 'example';

                    /**
                     * Returns an array of the values defined in the cases
                     *
                     * @return array<int, string>
                     */
                    public static function values(): array
                    {
                        return array_map(fn(\$value) => \$value->value, self::cases());
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  ENUM: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  ENUM: The enumeration has been generated successfully."));

        return parent::SUCCESS;
    }
}
