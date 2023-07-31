<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmRunBuildCommand extends Command {

	use ConsoleOutput;

	protected static $defaultName = "npm:build";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to generate final dist of a certain vite type resource")
            ->addArgument("resource", InputArgument::REQUIRED, "Resource name");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$rsc = $input->getArgument("resource");
        $cmd = kernel->execute("cd resources/{$rsc}/ && npm run build", false);
        $output->writeln(arr->of($cmd)->join("\n"));
		return Command::SUCCESS;
	}

}
