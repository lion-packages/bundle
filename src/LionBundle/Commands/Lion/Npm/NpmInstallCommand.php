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
 * Install the Vite.JS project dependencies
 *
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmInstallCommand extends MenuCommand
{
    /**
     * [Kernel class object]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    #[Inject]
    public function setKernel(Kernel $kernel): NpmInstallCommand
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
            ->setName('npm:install')
            ->setDescription('Command to install dependencies with npm for a certain vite project')
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

        $packages = $this->str->of($this->arr->of($input->getArgument('packages'))->join(' '))->trim()->get();

        $this->kernel
            ->execute($this->str->of("cd resources/{$project}/ && npm install {$packages}")->trim()->get(), false);

        $output->writeln($this->warningOutput("\n\t>>  RESOURCES: {$project}"));

        if ('' != $packages) {
            $join = $this->arr->of(explode(' ', $packages))->join(', ');

            $output->writeln($this->successOutput("\t>>  RESOURCES: dependencies have been installed: {$join}"));
        } else {
            $output->writeln($this->successOutput("\t>>  RESOURCES: dependencies have been installed"));
        }

        return Command::SUCCESS;
    }
}
