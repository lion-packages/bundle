<?php 

namespace App\Console; 

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface; 
use Symfony\Component\Console\Helper\ProgressBar;
use LionSecurity\RSA;
use LionFiles\FILES;
use App\Http\Request\Request;

class RSACommand extends Command { 

	protected static $defaultName = "new:rsa";
	private object $env;

	protected function initialize(InputInterface $input, OutputInterface $output) {
		echo("Initializing RSA service... \r\n\n");
		$this->env = Request::getInstance()->env();
	} 

	protected function interact(InputInterface $input, OutputInterface $output) {
		RSA::$url_path = $this->env->RSA_URL_PATH === '' ? RSA::$url_path : $this->env->RSA_URL_PATH;
	}

	protected function configure() { 
		$this->setDescription("Command to create public and private keys with RSA");
	} 

	protected function execute(InputInterface $input, OutputInterface $output) {
		FILES::folder(RSA::$url_path);
		RSA::createKeys();
		FILES::remove('.rnd');

		$output->writeln("Public and private key generated correctly.");
		return Command::SUCCESS;
	} 

}