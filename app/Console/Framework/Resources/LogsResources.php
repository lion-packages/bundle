<?php

namespace App\Console\Framework\Resources;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogsResources extends Command
{
	use ConsoleOutput, ClassPath;

	protected static $defaultName = "resource:logs";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to generate the logs of all resources");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        foreach (Store::view("resources/", false) as $key => $resource) {
            $rsc = str->of($resource)->replace("resources/", "")->get();
            $this->new("storage/logs/resources/{$rsc}", "log");
            $this->add("");
            $this->force();
            $this->close();

            $output->writeln($this->warningOutput("\t>>  LOG: {$rsc}"));
            $output->writeln(
            	$this->successOutput("\t>>  LOG: log has been generated for the 'storage/logs/resources/{$rsc}.log' resource")
            );
        }

		return Command::SUCCESS;
	}
}
