<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TraitCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;

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
     * @required
     * */
    public function setStr(Str $str): TraitCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
    {
        $this
            ->setName('new:trait')
            ->setDescription('Command required for trait creation')
            ->addArgument('trait', InputArgument::OPTIONAL, 'Trait name', 'ExampleTrait');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $trait = $input->getArgument('trait');

        $this->classFactory->classFactory('app/Traits/', $trait);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
                $this->str->of('<?php')->ln()->ln()
                    ->concat('declare(strict_types=1);')->ln()->ln()
                    ->concat('namespace')->spaces(1)
                    ->concat("{$namespace};")->ln()->ln()
                    ->concat('trait')->spaces(1)
                    ->concat($class)->ln()
                    ->concat('{')->ln()->ln()
                    ->concat('}')->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  TRAIT: {$class}"));
        $output->writeln($this->successOutput("\t>>  TRAIT: the '{$namespace}\\{$class}' trait has been generated"));

        return Command::SUCCESS;
    }

}
