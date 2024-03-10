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

class ExceptionsCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
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

    protected function configure(): void
    {
        $this
            ->setName('new:exception')
            ->setDescription('Command required to create an exception')
            ->addArgument('exception', InputArgument::OPTIONAL, 'Exception name', 'ExampleException');
    }

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
