<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InterfaceCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
        $this->classFactory = new ClassFactory();
        $this->store = new Store();
	}

	protected function configure(): void
	{
		$this
            ->setName('new:interface')
            ->setDescription('Command required for interface creation')
            ->addArgument('interface', InputArgument::OPTIONAL, 'Interface name', 'ExampleInterface');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$interface = $input->getArgument('interface');

        $this->classFactory->classFactory('app/Interfaces/', $interface);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
            	Str::of("<?php")->ln()->ln()
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

        $output->writeln($this->warningOutput("\t>>  INTERFACE: {$interface}"));

        $output->writeln(
        	$this->successOutput("\t>>  INTERFACE: the '{$namespace}\\{$class}' interface has been generated")
        );

		return Command::SUCCESS;
	}
}
