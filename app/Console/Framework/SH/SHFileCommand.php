<?php

namespace App\Console\Framework\SH;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SHFileCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "sh:new";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to create files with extension sh")
            ->addArgument("sh", InputArgument::OPTIONAL, 'SH name', "Example");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $sh = $input->getArgument("sh");

        $this->new("storage/cron/{$sh}", "sh");
        $this->add("# Add the instructions you want to execute \n");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  SH: {$sh}"));
        $output->writeln($this->successOutput("\t>>  SH: File generated successfully"));
		return Command::SUCCESS;
	}
}
