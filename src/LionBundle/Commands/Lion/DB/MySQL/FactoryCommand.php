<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB\MySQL;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Helpers\Str;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FactoryCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): FactoryCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): FactoryCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): FactoryCommand
    {
        $this->str = $str;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('db:mysql:factory')
            ->setDescription('Command required for the creation of new factories')
            ->addArgument('factory', InputArgument::OPTIONAL, 'Factory name', 'ExampleFactory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory = $input->getArgument('factory');

        $this->classFactory->classFactory('database/Factory/', $factory);
        $folder = $this->classFactory->getFolder();
        $namespace = $this->classFactory->getNamespace();
        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add($this->str->of("<?php")->ln()->ln()->concat('declare(strict_types=1);')->ln()->ln()->get())
            ->add($this->str->of("namespace ")->concat($namespace)->concat(";")->ln()->ln()->get())
            ->add($this->str->of('use Lion\Bundle\Interface\FactoryInterface;')->ln()->ln()->get())
            ->add(
                $this->str->of("class ")->concat($class)->concat(' implements FactoryInterface')->concat("\n{")->ln()->get()
            )
            ->add("\t/**\n")
            ->add("\t * {@inheritdoc}\n")
            ->add("\t **/\n")
            ->add("\tpublic static function definition(): array\n\t{\n\t\treturn [fake()->name()];\n\t}\n")
            ->add("}\n")
            ->close();

        $output->writeln($this->warningOutput("\t>>  FACTORY: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  FACTORY: the '{$namespace}\\{$class}' factory has been generated")
        );

        return Command::SUCCESS;
    }
}
