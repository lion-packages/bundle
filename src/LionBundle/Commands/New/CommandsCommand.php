<?php

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandsCommand extends Command
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
            ->setName('new:command')
            ->setDescription('Command required for the creation of new Commands')
            ->addArgument('new-command', InputArgument::OPTIONAL, 'Command name', 'ExampleCommand');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $input->getArgument('new-command');

        $this->classFactory->classFactory('app/Console/Commands/', $command);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("use LionCommand\Command;\n")
            ->add("use Symfony\Component\Console\Input\InputInterface;\n")
            ->add("use Symfony\Component\Console\Output\OutputInterface;\n\n")
            ->add("class {$class} extends Command\n{\n")
            ->add("\t" . 'protected function initialize(InputInterface $input, OutputInterface $output): void' . "\n\t" . '{' . "\n\n\t}\n\n")
            ->add("\t" . 'protected function interact(InputInterface $input, OutputInterface $output): void' . "\n\t" . '{'. "\n\n\t}\n\n")
            ->add("\t" . "protected function configure(): void\n\t{\n\t\t" . '$this->setName("example:command")->setDescription("");' . "\n\t}\n\n")
            ->add("\t" . 'protected function execute(InputInterface $input, OutputInterface $output): int' . "\n\t" . '{'. "\n")
            ->add("\t\t" . '$output->writeln("");' . "\n\n\t\t" . 'return Command::SUCCESS;' . "\n")
            ->add("\t}\n}")
            ->close();

        $output->writeln($this->warningOutput("\t>>  COMMAND: {$command}"));
        $output->writeln($this->successOutput("\t>>  COMMAND: the '{$namespace}\\{$class}' command has been generated"));

        return Command::SUCCESS;
	}
}
