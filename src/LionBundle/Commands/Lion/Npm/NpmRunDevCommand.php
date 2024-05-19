<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\ProcessCommand;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the local vite environment for development
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmRunDevCommand extends MenuCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('npm:dev')
            ->setDescription('Starts the Vite development server');
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

        ProcessCommand::run("cd ./vite/{$project} && npm run dev");

        return Command::SUCCESS;
    }
}
