<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmUninstallCommand extends Command {

	use ConsoleOutput;

	protected static $defaultName = "npm:uninstall";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to uninstall dependencies with npm for a certain resource")
            ->addArgument("resource", InputArgument::REQUIRED, "Resource name")
            ->addArgument("packages", InputArgument::REQUIRED, "Package name");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$rsc = $input->getArgument("resource");
        $pkg = $input->getArgument("packages");

        $cmd = kernel->execute("cd resources/{$rsc}/ && npm uninstall {$pkg}", false);
        $output->writeln(arr->of($cmd)->join("\n"));
        return Command::SUCCESS;
	}

}
