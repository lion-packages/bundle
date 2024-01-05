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

class EnumCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
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

	protected function configure(): void
    {
		$this
            ->setName('new:enum')
            ->setDescription('Command required for creating new Enums')
            ->addArgument('enum', InputArgument::OPTIONAL, 'Enum name', 'ExampleEnum');
	}

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
                    ->concat("namespace")->spaces(1)
                    ->concat($namespace)
                    ->concat(";")->ln()->ln()
                    ->concat("enum")->spaces(1)
                    ->concat($class)
                    ->concat(": string")->ln()
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

        $output->writeln($this->warningOutput("\t>>  ENUM: {$enum}"));
        $output->writeln($this->successOutput("\t>>  ENUM: the '{$namespace}\\{$class}' enum has been generated"));

        return Command::SUCCESS;
	}
}
