<?php

namespace App\Console\Framework\SSH;

use App\Traits\Framework\ClassPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SSHFileCommand extends Command {

    use ClassPath;

	protected static $defaultName = "sh:new";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to create files with extension sh")
            ->addArgument("sh", InputArgument::OPTIONAL, 'SH name', "Example");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $sh = $input->getArgument("sh");

        $this->new("storage/cron/{$sh}", "sh");
        $this->add("# Add the instructions you want to execute \n");
        $this->force();
        $this->close();

        $output->writeln("<comment>\t>>  SH: {$sh}</comment>");
        $output->writeln("<info>\t>>  SH: File generated successfully</info>");

		return Command::SUCCESS;
	}

}
