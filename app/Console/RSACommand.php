<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionSecurity\RSA;
use LionFiles\FILES;

class RSACommand extends Command {

	protected static $defaultName = "key:rsa";

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$output->writeln("<comment>Initializing RSA service...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			"Command to create public and private keys with RSA"
		)->addOption(
            'path', null, InputOption::VALUE_REQUIRED, 'Save to a specific path?'
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getOption('path');
        if ($path === null) {
            if ($_ENV['RSA_URL_PATH'] != '') RSA::$url_path = $_ENV['RSA_URL_PATH'];
        } else {
            RSA::$url_path = $path;
        }

		FILES::folder(RSA::$url_path);
		RSA::createKeys();
		FILES::remove('.rnd');

		$output->writeln("<info>Public and private key created successfully</info>");
		return Command::SUCCESS;
	}

}