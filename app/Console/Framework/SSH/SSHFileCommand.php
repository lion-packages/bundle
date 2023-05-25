<?php

namespace App\Console\Framework\SSH;

use App\Traits\Framework\ClassPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SSHFileCommand extends Command {

	protected static $defaultName = "sh:new";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>creating sh file...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to create files with extension sh")
            ->addArgument("sh", InputArgument::REQUIRED, 'SH name', null);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        ClassPath::new("storage/cron/{$input->getArgument("sh")}", "sh");
        ClassPath::add("# Add the instructions you want to execute \n");
        ClassPath::force();
        ClassPath::close();
		$output->writeln("<info>File generated successfully</info>");
		return Command::SUCCESS;
	}

}
