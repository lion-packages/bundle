<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmUninstallCommand extends MenuCommand
{
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmUninstallCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:uninstall')
            ->setDescription('Command to uninstall dependencies with npm from a vite project')
            ->addArgument('packages', InputArgument::REQUIRED, 'Package name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);
        $packages = $input->getArgument('packages');

        $this->kernel->execute(
            "cd vite/{$project}/ && npm uninstall {$packages} > /dev/null 2>&1 || npm uninstall {$packages} > nul 2>&1",
            false
        );

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput(
            "\t>>  VITE: dependencies have been uninstalled: {$this->arr->of(explode(' ', $packages))->join(', ')}"
        ));

        return Command::SUCCESS;
	}
}
