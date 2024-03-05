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

class InterfaceCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;

    /**
     * @required
     */
    public function setClassFactory(ClassFactory $classFactory): InterfaceCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): InterfaceCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): InterfaceCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('new:interface')
            ->setDescription('Command required for interface creation')
            ->addArgument('interface', InputArgument::OPTIONAL, 'Interface name', 'ExampleInterface');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $interface = $input->getArgument('interface');

        $this->classFactory->classFactory('app/Interfaces/', $interface);
        $folder = $this->classFactory->getFolder();
        $namespace = $this->classFactory->getNamespace();
        $class = $this->classFactory->getClass();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
                $this->str->of("<?php")->ln()->ln()
                    ->concat('declare(strict_types=1);')->ln()->ln()
                    ->concat('namespace')->spaces(1)
                    ->concat("{$namespace};")->ln()->ln()
                    ->concat('interface')->spaces(1)
                    ->concat($class)->ln()
                    ->concat('{')->ln()->ln()
                    ->concat("}")->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  INTERFACE: {$class}"));

        $output->writeln(
            $this->successOutput(
                "\t>>  INTERFACE: the '{$namespace}\\{$class}' interface has been generated"
            )
        );

        return Command::SUCCESS;
	}
}
