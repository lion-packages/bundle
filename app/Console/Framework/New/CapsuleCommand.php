<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CapsuleCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "new:capsule";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command required for creating new custom capsules")
            ->addArgument('capsule', InputArgument::OPTIONAL, 'Capsule name', "Example");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $capsule = $input->getArgument('capsule');
		$list = $this->export("database/Class/", $capsule);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);

        $this->add(
            str->of("<?php")->ln()->ln()
                ->concat('declare(strict_types=1);')->ln()->ln()
                ->concat("namespace")->spaces(1)
                ->concat($list['namespace'])
                ->concat(";")->ln()->ln()
                ->concat("class")->spaces(1)
                ->concat($list['class'])->spaces(1)->ln()
                ->concat("{")->ln()->ln()
                ->concat("}")
                ->get()
        );

        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$capsule}"));
        $output->writeln($this->successOutput("\t>>  CAPSULE: The '{$list['namespace']}\\{$list['class']}' capsule has been generated"));

        return Command::SUCCESS;
	}
}
