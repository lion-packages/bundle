<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\SH;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SHFileCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): SHFileCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): SHFileCommand
    {
        $this->store = $store;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('sh:new')
            ->setDescription('Command to create files with extension sh')
            ->addArgument('sh', InputArgument::OPTIONAL, 'SH name', 'Example');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $sh = $input->getArgument('sh');

        $this->store->folder('storage/sh/');
        $this->classFactory->create($sh, 'sh', 'storage/sh/')->add("#!/bin/bash\n")->close();

        $output->writeln($this->warningOutput("\t>>  SH: {$sh}"));
        $output->writeln($this->successOutput("\t>>  SH: File generated successfully"));

		return Command::SUCCESS;
	}
}
