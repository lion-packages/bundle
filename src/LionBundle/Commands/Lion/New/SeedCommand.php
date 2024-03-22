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
 * Generate a seed
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class SeedCommand extends Command
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
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): SeedCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): SeedCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): SeedCommand
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
            ->setName('new:seed')
            ->setDescription('Command required for creating new seeds')
            ->addArgument('seed', InputArgument::OPTIONAL, 'Name seed', 'ExampleSeed');
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
        $this->classFactory->classFactory('database/Seed/', $input->getArgument('seed'));

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add($this->str->of("<?php")->ln()->ln()->concat('declare(strict_types=1);')->ln()->ln()->get())
            ->add($this->str->of("namespace ")->concat($namespace)->concat(";")->ln()->ln()->get())
            ->add($this->str->of("use Lion\Bundle\Interface\SeedInterface;")->ln()->get())
            ->add($this->str->of("use Lion\Database\Drivers\MySQL as DB;")->ln()->ln()->get())
            ->add($this->str->of("class ")->concat($class)->concat(' implements SeedInterface')->ln()->concat("{")->ln()->get())
            ->add($this->str->lt()->concat('const INDEX = null;')->ln()->ln()->get())
            ->add("\t/**\n\t * {@inheritdoc}\n\t **/\n")
            ->add("\tpublic function run(): object\n\t{\n\t\treturn success('run seed');\n\t}\n}\n")
            ->close();

        $output->writeln($this->warningOutput("\t>>  SEED: {$class}"));

        $output->writeln($this->successOutput("\t>>  SEED: the '{$namespace}\\{$class}' seed has been generated"));

        return Command::SUCCESS;
    }
}
