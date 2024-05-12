<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a Request class
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
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
    const METHODS = ['create', 'read', 'update', 'delete'];

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
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): ModelCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): ModelCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model = $input->getArgument('model');

        $this->classFactory->classFactory('app/Models/', $model);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("use Lion\Database\Drivers\MySQL as DB;\n\n")
            ->add(
                <<<EOT
                /**
                 * Description of Model '{$class}'
                 *
                 * @package {$namespace}
                 */
                class {$class}
                {\n
                EOT
            );

        foreach (self::METHODS as $method) {
            $customMethod = $this->classFactory->getCustomMethod(
                $this->str->of($method . $class)->replace('Model', '')->replace('model', '')->concat('DB')->get(),
                $method === 'read' ? 'array|object' : 'object',
                '',
                $method === 'read' ? "return DB::view('')->select()->getAll();" : "return DB::call('', [])->execute();",
                'public',
                $method === 'delete' ? 1 : 2
            );

            $this->classFactory->add($customMethod);
        }

        $this->classFactory->add("}\n")->close();

        $output->writeln($this->warningOutput("\t>>  MODEL: {$class}"));

        $output->writeln($this->successOutput("\t>>  MODEL: the '{$namespace}\\{$class}' model has been generated"));

        return Command::SUCCESS;
    }
}
