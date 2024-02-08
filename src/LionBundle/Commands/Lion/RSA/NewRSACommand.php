<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\RSA;

use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Security\RSA;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewRSACommand extends Command
{
    private RSA $rsa;
    private Store $store;

    /**
     * @required
     * */
    public function setRSA(RSA $rsa): NewRSACommand
    {
        $this->rsa = $rsa;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): NewRSACommand
    {
        $this->store = $store;

        return $this;
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

        $this->rsa->setUrlPath(null === $path ? $this->rsa->getUrlPath() : storage_path($path, false));
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
