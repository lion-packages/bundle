<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Kernel;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update Vite.JS project dependencies
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmUpdateCommand extends MenuCommand
{
    /**
     * [Adds functions to execute commands, allows you to create an Application
     * object to run applications with your custom commands]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    #[Inject]
    public function setKernel(Kernel $kernel): NpmUpdateCommand
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
            ->setName('npm:update')
            ->setDescription('Command to install dependencies with npm for a vite project');
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
     * @return int
     *
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $this->selectedProject($input, $output);

        $this->kernel->execute("cd resources/{$project}/ && npm update");

        $output->writeln($this->warningOutput("\n\t>>  RESOURCES: {$project}"));

        $output->writeln($this->successOutput("\t>>  RESOURCES: dependencies have been updated"));

        return parent::SUCCESS;
    }
}
