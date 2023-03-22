<?php

namespace App\Console\Framework\Key;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionSecurity\RSA;
use LionFiles\Store;

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

        RSA::$url_path = $path === null ? RSA::$url_path : storage_path($path, false);
		Store::folder(RSA::$url_path);
		RSA::createKeys();
		Store::remove('.rnd');

		$output->writeln("<info>Public and private key created successfully</info>");
		return Command::SUCCESS;
	}

}