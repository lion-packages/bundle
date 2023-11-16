<?php

declare(strict_types=1);

namespace LionBundle\Commands\RSA;

use LionCommand\Command;
use LionFiles\Store;
use LionSecurity\RSA;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewRSACommand extends Command
{
    private RSA $rsa;
    private Store $store;

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
        $this->rsa = new RSA();
        $this->store = new Store();
	}

	protected function configure(): void
	{
		$this
            ->setName('rsa:new')
            ->setDescription('Command to create public and private keys with RSA')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $path = $input->getOption('path');

        $this->rsa->setUrlPath($path === null ? $this->rsa->getUrlPath() : storage_path($path, false));
        $this->store->folder($this->rsa->getUrlPath());
        $this->rsa->create();

        if (isSuccess($this->store->exist('.rnd'))) {
            $this->store->remove('.rnd');
        }

        $output->writeln($this->warningOutput("\t>>  RSA KEYS: public and private"));
        $output->writeln($this->successOutput("\t>>  RSA KEYS: Exported in {$this->rsa->getUrlPath()}public.key"));
        $output->writeln($this->successOutput("\t>>  RSA KEYS: Exported in {$this->rsa->getUrlPath()}private.key"));

		return Command::SUCCESS;
	}
}
