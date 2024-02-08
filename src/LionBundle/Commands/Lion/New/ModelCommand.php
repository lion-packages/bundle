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

class ModelCommand extends Command
{
    const METHODS = ['create', 'read', 'update', 'delete'];

    private ClassFactory $classFactory;
    private Store $store;
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

	protected function configure(): void
	{
		$this
            ->setName('new:model')
            ->setDescription('Command required for the creation of new Models')
            ->addArgument('model', InputArgument::OPTIONAL, 'Model name', 'ExampleModel');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $model = $input->getArgument('model');

        $this->classFactory->classFactory('app/Models/', $model);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

		$this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("use Lion\Database\Drivers\MySQL as DB;\n\n")
            ->add("class {$class}\n{\n");

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
