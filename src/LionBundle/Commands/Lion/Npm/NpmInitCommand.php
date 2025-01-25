<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Command\Kernel;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initialize a project with Vite.JS
 *
 * @property Kernel $Kernel [Adds functions to execute commands, allows you to
 * create an Application object to run applications with your custom commands]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmInitCommand extends MenuCommand
{
    /**
     * [List of templates available to create electron-vite projects]
     *
     * @const VITE_ELECTRON_TEMPLATES
     */
    private const array VITE_ELECTRON_TEMPLATES = [
        'Vanilla',
        'Vue',
        'React',
        'Svelte',
        'Solid',
    ];

    /**
     * [List of templates available to create vite projects]
     *
     * @const VITE_TEMPLATES
     */
    private const array VITE_TEMPLATES = [
        'Vanilla',
        'Vue',
        'React',
        'Preact',
        'Lit',
        'Svelte',
        'Solid',
        'Qwik',
        'Electron',
    ];

    /**
     * [List of languages available to create a project]
     *
     * @const TYPES
     */
    private const array TYPES = [
        'js',
        'ts',
    ];

    /**
     * [Adds functions to execute commands, allows you to create an Application
     * object to run applications with your custom commands]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    #[Inject]
    public function setKernel(Kernel $kernel): NpmInitCommand
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
            ->setName('npm:init')
            ->setDescription('Command to create Javascript projects with Vite.JS')
            ->addArgument('project', InputArgument::OPTIONAL, "Project's name", 'app');
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
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $this->str->of($input->getArgument('project'))->trim()->replace('_', '-')->replace(' ', '-')->get();

        if (isSuccess($this->store->exist("resources/{$project}/"))) {
            $output->writeln($this->warningOutput("\t>>  RESOURCES: a resource with this name already exists"));

            return Command::FAILURE;
        }

        $this->store->folder('resources/');

        $template = $this->str
            ->of($this->selectedTemplate($input, $output, self::VITE_TEMPLATES))
            ->lower()
            ->get();

        if ('electron' === $template) {
            $electronTemplate = $this->str
                ->of($this->selectedTemplate($input, $output, self::VITE_ELECTRON_TEMPLATES))
                ->lower()
                ->get();

            $this->createElectronViteProject($input, $output, $project, $electronTemplate);
        } else {
            $this->createViteProject($input, $output, $project, $template);
        }

        $output->writeln($this->warningOutput("\t>>  RESOURCES: {$project}"));

        $output->writeln(
            $this->successOutput("\t>>  RESOURCES: 'resources/{$project}/' project has been generated successfully")
        );

        return Command::SUCCESS;
    }

    /**
     * Create a vite project and install its dependencies
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param string $project [Project's name]
     * @param string $template [Project template]
     *
     * @return void
     */
    private function createViteProject(
        InputInterface $input,
        OutputInterface $output,
        string $project,
        string $template
    ): void {
        $type = $this->selectedTypes($input, $output, self::TYPES);

        $commandCreate = "cd resources/ && echo | npm create vite@latest {$project}";

        $commandCreate .= " -- --template {$template}" . ('js' === $type ? '' : '-ts');

        $this->kernel->execute($this->str->of($commandCreate)->trim()->get(), false);
    }

    /**
     * Create an electron-vite project and install its dependencies
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param string $project [Project's name]
     * @param string $template [Project template]
     *
     * @return void
     */
    private function createElectronViteProject(
        InputInterface $input,
        OutputInterface $output,
        string $project,
        string $template
    ): void {
        $type = $this->selectedTypes($input, $output, self::TYPES);

        $commandCreate = "cd resources/ && echo | npm create @quick-start/electron";

        $commandCreate .= " {$project} -- --template {$template}" . ('js' === $type ? '' : '-ts') . ' --skip';

        $this->kernel->execute($commandCreate, false);
    }
}
