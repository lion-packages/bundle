<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Kernel;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install the project dependencies using NPM.
 */
class NpmInstallCommand extends MenuCommand
{
    /**
     * Adds functions to execute commands, allows you to create an Application
     * object to run applications with your custom commands.
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
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('npm:install')
            ->setDescription('Command to install dependencies with npm for a certain vite project.')
            ->addArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Package name.', [])
            ->addOption('dev', 'D', InputOption::VALUE_NONE, 'Install packages as devDependencies (--save-dev).');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return int
     *
     * @throws Exception If there are no projects available.
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $this->selectedProject();

        /** @var bool $isDev */
        $isDev = $input->getOption('dev');

        /** @var array<int, string> $packagesList */
        $packagesList = $input->getArgument('packages');

        /** @var string $packages */
        $packages = $this->str
            ->of(
                $this->arr
                    ->of($packagesList)
                    ->join(' ')
            )
            ->trim()
            ->get();

        $devFlag = $isDev ? '--save-dev' : '';

        /** @var string $command */
        $command = $this->str
            ->of("cd resources/{$project}/ && npm install --silent {$devFlag} {$packages}")
            ->trim()
            ->get();

        $this->kernel->execute($command);

        $output->writeln($this->warningOutput("\n\t>>  RESOURCES: {$project}"));

        if ('' != $packages) {
            $join = $this->arr->of(explode(' ', $packages))->join(', ');

            $devText = $isDev ? ' (devDependencies)' : '';

            $output->writeln(
                $this->successOutput("\t>>  RESOURCES: Dependencies have been installed{$devText}: {$join}")
            );
        } else {
            $output->writeln($this->successOutput("\t>>  RESOURCES: Dependencies have been installed"));
        }

        return parent::SUCCESS;
    }
}
