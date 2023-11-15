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

class TestCommand extends Command
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
            ->setName('new:test')
            ->setDescription('Command required for the creation of new test')
            ->addArgument('test', InputArgument::OPTIONAL, 'Test name', 'ExampleTest');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
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
            ->add("use PHPUnit\Framework\TestCase;\n\n")
            ->add(
                Str::of("class ")
                    ->concat($class)
                    ->concat(' extends TestCase ')->ln()
                    ->concat('{')->ln()
                    ->lt()->concat("public function setUp(): void ")->ln()
                    ->lt()->concat('{')->ln()->ln()
                    ->lt()->concat("}")->ln()->ln()
                    ->lt()->concat("public function tearDown(): void ")->ln()
                    ->lt()->concat('{')->ln()->ln()
                    ->lt()->concat("}")->ln()
                    ->concat("}")->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  TEST: {$test}"));
        $output->writeln($this->successOutput("\t>>  TEST: the '{$namespace}\\{$class}' test has been generated"));

        return Command::SUCCESS;
    }
}
