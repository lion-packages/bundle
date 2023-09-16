<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmUpdateCommand extends Command
{
	use ConsoleOutput;

	protected static $defaultName = "npm:update";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to install dependencies with npm for a vite project")
            ->addArgument("project", InputArgument::REQUIRED, "Project name");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$project = $input->getArgument("project");
        $cmd = kernel->execute("cd vite/{$project}/ && npm update", false);
        $output->writeln(arr->of($cmd)->join("\n"));
		return Command::SUCCESS;
	}
}
