<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Uninstall the Vite.JS project dependencies
 *
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmUninstallCommand extends MenuCommand
{
    /**
     * [Kernel class object]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmUninstallCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
	protected function configure(): void
	{
		$this
            ->setName('npm:uninstall')
            ->setDescription('Command to uninstall dependencies with npm from a vite project')
            ->addArgument('packages', InputArgument::REQUIRED, 'Package name');
	}

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);

        $packages = $input->getArgument('packages');

        $this->kernel->execute(
            "cd {$this->store->normalizePath("./vite/{$project}/")} && npm uninstall {$packages}",
            false
        );

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));

        $output->writeln($this->successOutput(
            "\t>>  VITE: dependencies have been uninstalled: {$this->arr->of(explode(' ', $packages))->join(', ')}"
        ));

        return Command::SUCCESS;
	}
}
