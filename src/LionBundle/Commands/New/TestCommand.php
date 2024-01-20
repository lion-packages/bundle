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

class TestCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;

	/**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): TestCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): TestCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): TestCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('new:test')
            ->setDescription('Command required for the creation of new test')
            ->addArgument('test', InputArgument::OPTIONAL, 'Test name', 'ExampleTest');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $test = $input->getArgument('test');

        $this->classFactory->classFactory('tests/', $test);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

		$this->store->folder($folder);

		$this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("use Lion\Test\Test;\n\n")
            ->add(
                $this->str->of("class ")
                    ->concat($class)
                    ->concat(' extends Test')->ln()
                    ->concat('{')->ln()
                    ->lt()->concat("protected function setUp(): void ")->ln()
                    ->lt()->concat('{')->ln()->ln()
                    ->lt()->concat("}")->ln()->ln()
                    ->lt()->concat("protected function tearDown(): void ")->ln()
                    ->lt()->concat('{')->ln()->ln()
                    ->lt()->concat("}")->ln()
                    ->concat("}")->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  TEST: {$class}"));
        $output->writeln($this->successOutput("\t>>  TEST: the '{$namespace}\\{$class}' test has been generated"));

        return Command::SUCCESS;
    }
}
