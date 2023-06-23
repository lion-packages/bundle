<?php

namespace App\Console\Framework\RSA;

use LionFiles\Store;
use LionSecurity\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class NewRSACommand extends Command {

	protected static $defaultName = "rsa:new";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to create public and private keys with RSA")
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getOption('path');
        RSA::$url_path = $path === null ? RSA::$url_path : storage_path($path, false);
		Store::folder(RSA::$url_path);
		RSA::createKeys();

        if (isSuccess(Store::exist(".rnd"))) {
            Store::remove('.rnd');
        }

        $output->writeln("<comment>\t>>  RSA KEYS: public and private</comment>");
        $output->writeln("<info>\t>>  RSA KEYS: Exported in " . RSA::$url_path . "public.key</info>");
        $output->writeln("<info>\t>>  RSA KEYS: Exported in " . RSA::$url_path . "private.key</info>");

		return Command::SUCCESS;
	}

}
