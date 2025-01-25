<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Uninstall the Vite.JS project dependencies
 *
 * @property Kernel $Kernel [Adds functions to execute commands, allows you to
 * create an Application object to run applications with your custom commands]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmUninstallCommand extends MenuCommand
{
    /**
     * [Adds functions to execute commands, allows you to create an Application
     * object to run applications with your custom commands]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    #[Inject]
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
            ->addArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Package name', []);
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

        $packages = $this->arr->of($input->getArgument('packages'))->join(' ');

        $packages = $this->str->of($packages)->trim()->get();

        $this->kernel->execute(
            $this->str->of("cd resources/{$project}/ && npm uninstall {$packages}")->trim()->get(),
            false
        );

        $output->writeln($this->warningOutput("\n\t>>  RESOURCES: {$project}"));

        $output->writeln($this->successOutput(
            "\t>>  RESOURCES: dependencies have been uninstalled: {$this->arr->of(explode(' ', $packages))->join(', ')}"
        ));

        return Command::SUCCESS;
    }
}
