<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InterfaceCommand extends Command {

    use ClassPath, ConsoleOutput;

	protected static $defaultName = "new:interface";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command required for interface creation")
            ->addArgument('interface', InputArgument::OPTIONAL, 'Interface name', "ExampleInterface");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$interface = $input->getArgument('interface');
        $list = $this->export("app/Interfaces/", $interface);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add(str->of("<?php")->ln()->ln()->get());
        $this->add(str->of("namespace ")->concat($list['namespace'])->concat(";")->ln()->ln()->get());
        $this->add(str->of("interface ")->concat($list['class'])->concat(" {")->ln()->ln()->concat("}")->get());
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  INTERFACE: {$interface}"));
        $output->writeln($this->successOutput("\t>>  INTERFACE: The '{$list['namespace']}\\{$list['class']}' interface has been generated"));
		return Command::SUCCESS;
	}

}
