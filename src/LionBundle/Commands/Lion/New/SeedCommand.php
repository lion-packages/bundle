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

class SeedCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
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

    protected function configure(): void
    {
        $this
            ->setName('new:seed')
            ->setDescription('Command required for creating new seeds')
            ->addArgument('seed', InputArgument::OPTIONAL, 'Name seed', 'ExampleSeed');
    }

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
