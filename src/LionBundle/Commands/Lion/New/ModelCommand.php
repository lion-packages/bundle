<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Files\Store;
use Lion\Helpers\Str;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a Request class
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class ModelCommand extends Command
{
    /**
     * [List of methods generated in the class]
     *
     * @const METHODS
     */
    private const array METHODS = [
        'create',
        'read',
        'update',
        'delete'
    ];

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

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): ModelCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): ModelCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): ModelCommand
    {
        $this->str = $str;

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
            ->setName('new:model')
            ->setDescription('Command required for the creation of new Models')
            ->addArgument('model', InputArgument::OPTIONAL, 'Model name', 'ExampleModel');
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
        /** @var string $model */
        $model = $input->getArgument('model');

        $this->classFactory->classFactory('app/Models/', $model);

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

                use Lion\Database\Drivers\MySQL as DB;
                use Lion\Database\Interface\DatabaseCapsuleInterface;
                use stdClass;

                /**
                 * Description of Model '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class}
                {\n
                PHP
            );

        foreach (self::METHODS as $method) {
            /** @var string $methodName */
            $methodName = $this->str
                ->of($method . $class)
                ->replace('Model', '')
                ->replace('model', '')
                ->concat('DB')
                ->get();

            $methodType = $method === 'read'
                ? [
                    'type' => 'array|stdClass',
                    'annotation' => 'array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass',
                ]
                : 'int|stdClass';

            $methodBody = $method === 'read'
                ? "return DB::table('')\n\t\t\t->select()\n\t\t\t->getAll();"
                : "return DB::call('', [])\n\t\t\t->execute();";

            $customMethod = $this->classFactory->getCustomMethod(
                $methodName,
                $methodType,
                '',
                $methodBody,
                'public',
                $method === 'delete' ? 1 : 2
            );

            $this->classFactory->add($customMethod);
        }

        $this->classFactory->add("}\n")->close();

        $output->writeln($this->warningOutput("\t>>  MODEL: {$class}"));

        $output->writeln($this->successOutput("\t>>  MODEL: the '{$namespace}\\{$class}' model has been generated"));

        return parent::SUCCESS;
    }
}
