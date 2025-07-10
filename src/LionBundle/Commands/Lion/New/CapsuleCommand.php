<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Capsule\CapsuleFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a capsule class and its defined properties
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CapsuleCommand extends Command
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

    /**
     * Manages the configuration and structure of a generated capsule class
     *
     * @var CapsuleFactory $capsuleFactory
     */
    private CapsuleFactory $capsuleFactory;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): CapsuleCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setCapsuleFactory(CapsuleFactory $capsuleFactory): CapsuleCommand
    {
        $this->capsuleFactory = $capsuleFactory;

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
            ->setName('new:capsule')
            ->setDescription('Command required for creating new custom capsules')
            ->addArgument('capsule', InputArgument::OPTIONAL, 'Capsule name', 'Example')
            ->addOption(
                'properties',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Defined properties for the capsule',
                []
            )
            ->addOption('entity', 'e', InputOption::VALUE_OPTIONAL, 'Entity name', '');
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
     * @throws Exception
     * @throws ExceptionInterface
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * -----------------------------------------------------------------------------
         * Initialize console parameters
         * -----------------------------------------------------------------------------
         * The parameters are provided directly from the console, this in order to
         * manipulate the body of the capsule class
         * -----------------------------------------------------------------------------
         */

        /** @var string $capsule */
        $capsule = $input->getArgument('capsule');

        /** @var array<int, string> $properties */
        $properties = $input->getOption('properties');

        /** @var string $entity */
        $entity = $input->getOption('entity');

        /**
         * -----------------------------------------------------------------------------
         * Class Factory Parameters
         * -----------------------------------------------------------------------------
         * Class factory parameters are used to obtain the precise data that defines the
         * class, such as the name and namespace
         * -----------------------------------------------------------------------------
         */

        $this->classFactory->classFactory('database/Class/', $capsule);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        /**
         * -----------------------------------------------------------------------------
         * Initializing parameters in the body
         * -----------------------------------------------------------------------------
         * Initializes the capsule class body parameters so that they are nested in the
         * construction
         * -----------------------------------------------------------------------------
         */

        /** @phpstan-ignore-next-line */
        $this->capsuleFactory->setApplication($this->getApplication());

        $this->capsuleFactory->setOutput($output);

        $this->capsuleFactory->setClass($class);

        $this->capsuleFactory->setNamespace($namespace);

        $this->capsuleFactory->setEntity($entity);

        $this->capsuleFactory->generateMethods($properties);

        $this->capsuleFactory->generateInterfaces();

        /**
         * -----------------------------------------------------------------------------
         * Capsule class body
         * -----------------------------------------------------------------------------
         * Builds the body of the capsule class, this with the defined parameters. The
         * logic that a Capsule class performs is nested
         * -----------------------------------------------------------------------------
         */

        $this->capsuleFactory->addingClassAndImplementations();

        $this->capsuleFactory->addProperties();

        $this->capsuleFactory->addAbstractMethods();

        if (count($properties) > 0) {
            $this->capsuleFactory
                ->getStr()
                ->ln()
                ->ln();

            $this->capsuleFactory->addMethods();
        } else {
            $this->capsuleFactory
                ->getStr()
                ->ln();
        }

        /** @var string $body */
        $body = $this->capsuleFactory
            ->getStr()
            ->concat("}")
            ->ln()
            ->get();

        $this->capsuleFactory->clean();

        /**
         * -----------------------------------------------------------------------------
         * File manufacturing
         * -----------------------------------------------------------------------------
         * Creating the file with the content of the manufactured class
         * -----------------------------------------------------------------------------
         */

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add($body)
            ->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  CAPSULE: The capsule class has been generated successfully."));

        return parent::SUCCESS;
    }
}
