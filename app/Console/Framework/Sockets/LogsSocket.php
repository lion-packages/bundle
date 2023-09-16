<?php

namespace App\Console\Framework\Sockets;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogsSocket extends Command
{
	use ConsoleOutput, ClassPath;

	protected static $defaultName = "socket:logs";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to generate the logs of all sockets");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		foreach (array_keys(kernel->getSockets()) as $key => $socket) {
            $this->new("storage/logs/sockets/{$socket}", "log");
            $this->add("");
            $this->force();
            $this->close();

            $output->writeln($this->warningOutput("\t>>  LOG: {$socket}"));
            $output->writeln($this->successOutput("\t>>  LOG: log has been generated for the 'storage/logs/sockets/{$socket}.log' resource"));
        }

		return Command::SUCCESS;
	}
}
