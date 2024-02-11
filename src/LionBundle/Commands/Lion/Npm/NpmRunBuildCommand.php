<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmRunBuildCommand extends MenuCommand
{
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmRunBuildCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:build')
            ->setDescription('Command to generate dist for a vite project');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);
        $this->kernel->execute("cd vite/{$project}/ && npm run build", false);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: project dist has been generated: ./vite/{$project}/"));

		return Command::SUCCESS;
	}
}
