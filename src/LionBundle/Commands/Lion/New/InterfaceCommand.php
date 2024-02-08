<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InterfaceCommand extends Command
{
    private Str $str;

    /**
     * @required
     * */
    public function setStr(Str $str): InterfaceCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('new:interface')
            ->setDescription('Command required for interface creation')
            ->addArgument('interface', InputArgument::OPTIONAL, 'Interface name', 'ExampleInterface');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $factory = new ClassCommandFactory(['interface']);

        return $factory->execute(function(ClassCommandFactory $classFactory, Store $store) use ($input, $output) {
            $interface = $input->getArgument('interface');

            $factoryInterface = $classFactory->getFactory('interface');
            $data = $classFactory->getData($factoryInterface, ['path' => 'app/Interfaces/', 'class' => $interface]);
            $store->folder($data->folder);

            $factoryInterface
                ->create($data->class, 'php', $data->folder)
                ->add(
                    $this->str->of("<?php")->ln()->ln()
                        ->concat('declare(strict_types=1);')->ln()->ln()
                        ->concat('namespace')->spaces(1)
                        ->concat("{$data->namespace};")->ln()->ln()
                        ->concat('interface')->spaces(1)
                        ->concat($data->class)->ln()
                        ->concat('{')->ln()->ln()
                        ->concat("}")->ln()
                        ->get()
                )
                ->close();

            $output->writeln($this->warningOutput("\t>>  INTERFACE: {$data->class}"));

            $output->writeln(
                $this->successOutput(
                    "\t>>  INTERFACE: the '{$data->namespace}\\{$data->class}' interface has been generated"
                )
            );

            return Command::SUCCESS;
        });
	}
}
