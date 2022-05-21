<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use LionSecurity\RSA;
use LionFiles\FILES;
use LionRequest\Request;

class RSACommand extends Command {

	protected static $defaultName = "key:rsa";
	private object $env;

	protected function initialize(InputInterface $input, OutputInterface $output) {
		$output->writeln("<comment>Initializing RSA service...</comment>");
		$this->env = Request::getInstance()->env();
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			"Command to create public and private keys with RSA"
		)->addArgument(
			'url-path', InputArgument::OPTIONAL, 'Save to a specific path?'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$url_path = $input->getArgument('url-path');
		if ($url_path) {
			RSA::$url_path = $url_path;
		} else {
			RSA::$url_path = $this->env->RSA_URL_PATH === '' ? RSA::$url_path : $this->env->RSA_URL_PATH;
		}

		FILES::folder(RSA::$url_path);
		RSA::createKeys();
		FILES::remove('.rnd');

		$output->writeln("<info>Public and private key created successfully</info>");
		return Command::SUCCESS;
	}

}