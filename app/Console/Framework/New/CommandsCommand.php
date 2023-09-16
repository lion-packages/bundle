<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandsCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "new:command";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription('Command required for the creation of new Commands')
            ->addArgument('new-command', InputArgument::OPTIONAL, 'Command name', "ExampleCommand");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('new-command');
		$list = $this->export("app/Console/Commands/", $command);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add("<?php\n\ndeclare(strict_types=1);\n\n");
        $this->add("namespace {$list['namespace']};\n\n");
        $this->add("use App\Traits\Framework\ConsoleOutput;\n");
        $this->add("use Symfony\Component\Console\Command\Command;\n");
        $this->add("use Symfony\Component\Console\Input\InputArgument;\n");
        $this->add("use Symfony\Component\Console\Input\InputInterface;\n");
        $this->add("use Symfony\Component\Console\Input\InputOption;\n");
        $this->add("use Symfony\Component\Console\Output\OutputInterface;\n\n");
        $this->add("class {$list['class']} extends Command\n{\n");
        $this->add("\t" . 'use ConsoleOutput;' . "\n\n");
        $this->add("\t" . 'protected static $defaultName = "";' . "\n\n");
        $this->add("\t" . 'protected function initialize(InputInterface $input, OutputInterface $output)' . "\n\t" . '{' . "\n\n\t}\n\n");
        $this->add("\t" . 'protected function interact(InputInterface $input, OutputInterface $output)' . "\n\t" . '{'. "\n\n\t}\n\n");
        $this->add("\t" . "protected function configure()\n\t{\n\t\t" . '$this->setDescription("");' . "\n\t}\n\n");
        $this->add("\t" . 'protected function execute(InputInterface $input, OutputInterface $output)' . "\n\t" . '{'. "\n");
        $this->add("\t\t" . '$output->writeln("");' . "\n\t\t" . 'return Command::SUCCESS;' . "\n");
        $this->add("\t}\n}");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  COMMAND: {$command}"));
        $output->writeln($this->successOutput("\t>>  COMMAND: the '{$list['namespace']}\\{$list['class']}' command has been generated"));

        return Command::SUCCESS;
	}
}
