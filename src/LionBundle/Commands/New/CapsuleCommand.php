<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use LionHelpers\Arr;
use LionHelpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CapsuleCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;
    private Arr $arr;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): CapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): CapsuleCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): CapsuleCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * @required
     * */
    public function setArr(Arr $arr): CapsuleCommand
    {
        $this->arr = $arr;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('new:capsule')
            ->setDescription('Command required for creating new custom capsules')
            ->addArgument('capsule', InputArgument::OPTIONAL, 'Capsule name', 'Example')
            ->addOption(
                'propierties',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Defined properties for the capsule',
                []
            );
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $capsule = $input->getArgument('capsule');
        $propierties = $input->getOption('propierties');

        $this->classFactory->classFactory('database/Class/', $capsule);
        $folder = $this->classFactory->getFolder();
        $namespace = $this->classFactory->getNamespace();
        $class = $this->classFactory->getClass();

        $listPropierties = [];
        $listMethods = [];

        foreach ($propierties as $key => $propierty) {
            $split = explode(':', $propierty);

            if (!empty($split[1])) {
                $data = $this->classFactory->getPrivatePropierty($split[0], $class, $split[1]);

                $listPropierties[] = $data->type;
                $listMethods[] = ['getter' => $data->getter->method, 'setter' => $data->setter->method];
            } else {
                $data = $this->classFactory->getPrivatePropierty($split[0], $class);

                $listPropierties[] = $data->type;
                $listMethods[] = ['getter' => $data->getter->method, 'setter' => $data->setter->method];
            }
        }

        $this->store->folder($folder);

        $str = $this->str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("namespace")->spaces(1)
            ->concat($namespace)
            ->concat(";")->ln()->ln()
            ->concat('use JsonSerializable;')->ln()->ln()
            ->concat("class")->spaces(1)
            ->concat($class)->spaces(1)
            ->concat('implements JsonSerializable')->ln()
            ->concat("{")->ln();

        if (count($propierties) > 0) {
            $str->lt()->concat($this->arr->of($listPropierties)->join("\n\t"))->ln()->ln();
        }

        $str
            ->lt()->concat('public function jsonSerialize(): array')->ln()
            ->lt()->concat('{')->ln()
            ->lt()->lt()->concat('return get_object_vars($this);')->ln()
            ->lt()->concat('}');

        if (count($propierties) > 0) {
            $str->ln()->ln();

            foreach ($listMethods as $key => $method) {
                if ($key === (count($listMethods) - 1)) {
                    $str->concat($method['getter'])->ln()->ln();
                    $str->concat($method['setter'])->ln();
                } else {
                    $str->concat($method['getter'])->ln()->ln();
                    $str->concat($method['setter'])->ln()->ln();
                }
            }
        } else {
            $str->ln();
        }

        $contentFile = $str->concat("}")->get();
        $this->classFactory->create($class, 'php', $folder)->add($contentFile)->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$capsule}"));
        $output->writeln($this->successOutput("\t>>  CAPSULE: the '{$namespace}\\{$class}' capsule has been generated"));

        return Command::SUCCESS;
	}
}
