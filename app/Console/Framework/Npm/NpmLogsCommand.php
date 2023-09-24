<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmLogsCommand extends Command
{
	use ConsoleOutput, ClassPath;

	protected static $defaultName = "npm:logs";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to generate the logs of all vite projects");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		foreach (Store::view("vite/", false) as $key => $project) {
            $pjt = str->of($project)->replace("vite/", "")->get();

            $this->new("storage/logs/vite/{$pjt}", "log");
            $this->add("");
            $this->force();
            $this->close();

            $output->writeln($this->warningOutput("\t>>  VITE LOG: {$pjt}"));
            $output->writeln($this->successOutput("\t>>  VITE LOG: a log has been generated for project 'storage/logs/vite/{$pjt}.log'"));
        }

		return Command::SUCCESS;
	}
}
