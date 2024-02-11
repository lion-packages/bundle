<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmInstallCommand extends MenuCommand
{
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmInstallCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:install')
            ->setDescription('Command to install dependencies with npm for a certain vite project')
            ->addArgument('packages', InputArgument::OPTIONAL, 'Package name', '');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);
        $packages = $input->getArgument('packages');

		$this->kernel->execute(
            "cd vite/{$project}/ && npm install {$packages} > /dev/null 2>&1 || npm install {$packages} > nul 2>&1",
            false
        );

        if ('' != $packages) {
            $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
            $output->writeln($this->successOutput(
                "\t>>  VITE: dependencies have been installed: {$this->arr->of(explode(' ', $packages))->join(', ')}"
            ));
        }

		return Command::SUCCESS;
	}
}
