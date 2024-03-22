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

/**
 * Generate Exception classes to handle exceptions
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class ExceptionsCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * @required
     */
    public function setClassFactory(ClassFactory $classFactory): ExceptionsCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): ExceptionsCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     */
    public function setStr(Str $str): ExceptionsCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new:exception')
            ->setDescription('Command required to create an exception')
            ->addArgument('exception', InputArgument::OPTIONAL, 'Exception name', 'ExampleException');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exception = $input->getArgument('exception');

        $this->classFactory->classFactory('app/Exceptions/', $exception);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add(
                $this->str->of("<?php")->ln()->ln()
                    ->concat('declare(strict_types=1);')->ln()->ln()
                    ->concat("namespace")->spaces(1)->concat($namespace)->concat(";")->ln()->ln()
                    ->concat('use Exception;')->ln()
                    ->concat('use JsonSerializable;')->ln()->ln()
                    ->concat("class")->spaces()->concat($class)->spaces()->concat(
                        "extends Exception implements JsonSerializable"
                    )->ln()
                    ->concat('{')->ln()
                    ->lt()->concat("public function jsonSerialize(): mixed")->ln()
                    ->lt()->concat('{')->ln()
                    ->lt()->lt()->concat('return error($this->getMessage(), $this->getCode(), [')->ln()
                    ->lt()->lt()->lt()->concat("'file' => ")->concat('$this->getFile(),')->ln()
                    ->lt()->lt()->lt()->concat("'line' => ")->concat('$this->getLine(),')->ln()
                    ->lt()->lt()->concat(']);')->ln()
                    ->lt()->concat("}")->ln()
                    ->concat("}")->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  EXCEPTION: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  EXCEPTION: the '{$namespace}\\{$class}' exception has been generated")
        );

        return Command::SUCCESS;
    }
}
