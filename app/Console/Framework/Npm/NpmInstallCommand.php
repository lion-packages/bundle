<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmInstallCommand extends Command
{
	use ConsoleOutput;

	protected static $defaultName = "npm:install";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{

	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to install dependencies with npm for a certain vite project")
            ->addArgument("project", InputArgument::REQUIRED, "Project name")
            ->addArgument("packages", InputArgument::OPTIONAL, "Package name", "");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $project = $input->getArgument("project");
        $pkg = $input->getArgument("packages");
		$cmd = kernel->execute("cd vite/{$project}/ && npm install {$pkg}", false);
        $output->writeln(arr->of($cmd)->join("\n"));
		return Command::SUCCESS;
	}
}
