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

	protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->classFactory = new ClassFactory();
        $this->store = new Store();
	}

	protected function configure(): void
    {
		$this
            ->setName('new:capsule')
            ->setDescription("Command required for creating new custom capsules")
            ->addArgument('capsule', InputArgument::OPTIONAL, 'Capsule name', "Example")
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
                $listMethods[] = ['getter' => $data->getter, 'setter' => $data->setter];
            } else {
                $data = $this->classFactory->getPrivatePropierty($split[0], $class);

                $listPropierties[] = $data->type;
                $listMethods[] = ['getter' => $data->getter, 'setter' => $data->setter];
            }
        }

        $this->store->folder($folder);

        $str = Str::of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("namespace")->spaces(1)
            ->concat($namespace)
            ->concat(";")->ln()->ln()
            ->concat("class")->spaces(1)
            ->concat($class)->ln()
            ->concat("{")->ln();

        if (count($propierties) > 0) {
            $str->lt()->concat(Arr::of($listPropierties)->join("\n\t"))->ln()->ln();

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
        $this->classFactory->create($class, 'php', "{$folder}/")->add($contentFile)->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$capsule}"));
        $output->writeln($this->successOutput("\t>>  CAPSULE: the '{$namespace}\\{$class}' capsule has been generated"));

        return Command::SUCCESS;
	}
}
