<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the dist of the Vite.JS project
 *
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmRunBuildCommand extends MenuCommand
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
    public function setKernel(Kernel $kernel): NpmRunBuildCommand
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
            ->setName('npm:build')
            ->setDescription('Command to generate dist for a vite project');
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

        $projectPath = $this->store->normalizePath("./vite/{$project}/");

        $this->kernel->execute("cd {$projectPath} && npm run build", false);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));

        $output->writeln($this->successOutput("\t>>  VITE: project dist has been generated: {$projectPath}"));

		return Command::SUCCESS;
	}
}
