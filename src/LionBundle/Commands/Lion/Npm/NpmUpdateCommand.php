<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmUpdateCommand extends MenuCommand
{
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmUpdateCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:update')
            ->setDescription('Command to install dependencies with npm for a vite project');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$project = $this->selectedProject($input, $output);
        $this->kernel->execute("cd vite/{$project}/ && npm update > /dev/null 2>&1 || npm update > nul 2>&1", false);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: dependencies have been updated"));

		return Command::SUCCESS;
	}
}
