<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MiddlewareCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

	/**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): MiddlewareCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): MiddlewareCommand
    {
        $this->store = $store;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('new:middleware')
            ->setDescription('Command required for the creation of new Middleware')
            ->addArgument('middleware', InputArgument::OPTIONAL, 'Middleware name', 'ExampleMiddleware');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $middleware = $input->getArgument('middleware');

        $this->classFactory->classFactory('app/Http/Middleware/', $middleware);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

		$this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("class {$class}\n{\n")
            ->add("\tpublic function __construct()\n\t{\n\n\t}\n}\n")
            ->close();

        $output->writeln($this->warningOutput("\t>>  MIDDLEWARE: {$middleware}"));

        $output->writeln(
        	$this->successOutput("\t>>  MIDDLEWARE: the '{$namespace}\\{$class}' middleware has been generated")
        );

		return Command::SUCCESS;
	}
}
