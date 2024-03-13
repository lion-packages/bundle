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
 * Generate an Enum
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class EnumCommand extends Command
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
     * */
    public function setClassFactory(ClassFactory $classFactory): EnumCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): EnumCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): EnumCommand
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
            ->setName('new:enum')
            ->setDescription('Command required for creating new Enums')
            ->addArgument('enum', InputArgument::OPTIONAL, 'Enum name', 'ExampleEnum');
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
		$enum = $input->getArgument('enum');

        $this->classFactory->classFactory('app/Enums/', $enum);

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
                    ->concat("enum")->spaces(1)->concat($class)->concat(": string")->ln()
                    ->concat('{')->ln()
                    ->lt()->concat("case EXAMPLE = 'example';")->ln()->ln()
                    ->lt()->concat("public static function values(): array")->ln()
                    ->lt()->concat('{')->ln()
                    ->lt()->lt()->concat('return array_map(fn($value) => $value->value, self::cases());')->ln()
                    ->lt()->concat("}")->ln()
                    ->concat("}")
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  ENUM: {$class}"));

        $output->writeln($this->successOutput("\t>>  ENUM: the '{$namespace}\\{$class}' enum has been generated"));

        return Command::SUCCESS;
	}
}
