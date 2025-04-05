<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Kernel;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initialize a project with Vite.JS/Astro
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmInitCommand extends MenuCommand
{
    /**
     * [List of available project templates]
     *
     * @const PROJECTS_TEMPLATE
     */
    private const array PROJECTS_TEMPLATE = [
        'Vite.JS',
        'Astro',
    ];
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

    /**
     * [Project name]
     *
     * @var string $project
     */
    private string $project;

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
            ->setDescription('Command to create Javascript projects with Vite.JS/Astro')
            ->addArgument('project', InputArgument::OPTIONAL, "Project's name", 'app');
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
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
        /** @var string $projectName */
        $projectName = $input->getArgument('project');

        /** @var string $project */
        $project = $this->str
            ->of($projectName)
            ->trim()
            ->replace('_', '-')
            ->replace(' ', '-')
            ->trim()
            ->get();

        $this->project = $project;

        if (isSuccess($this->store->exist("resources/{$this->project}/"))) {
            $output->writeln($this->warningOutput("\t>>  RESOURCES: a resource with this name already exists"));

            return parent::FAILURE;
        }

        $this->store->folder('resources/');

        $this->initProjects();

        $output->writeln($this->warningOutput("\t>>  RESOURCES: {$this->project}"));

        $output->writeln($this->successOutput(
            "\t>>  RESOURCES: 'resources/{$this->project}/' project has been generated successfully"
        ));

        return parent::SUCCESS;
    }

    /**
     * Initializes the projects to be created
     *
     * @return void
     */
    private function initProjects(): void
    {
        $projectType = $this->selectedTemplate($this->input, $this->output, self::PROJECTS_TEMPLATE, 'Vite.JS', 0);

        if ('Astro' === $projectType) {
            $this->createAstroProject();
        } else {
            /** @var string $template */
            $template = $this->str
                ->of($this->selectedTemplate($this->input, $this->output, self::VITE_TEMPLATES))
                ->lower()
                ->get();

            if ('electron' === $template) {
                /** @var string $electronTemplate */
                $electronTemplate = $this->str
                    ->of($this->selectedTemplate($this->input, $this->output, self::VITE_ELECTRON_TEMPLATES))
                    ->lower()
                    ->get();

                $this->createElectronViteProject($electronTemplate);
            } else {
                $this->createViteProject($template);
            }
        }
    }

    /**
     * Starting an Astro Project
     *
     * @return void
     */
    private function createAstroProject(): void
    {
        $command = "cd resources/ && echo | npm create astro@latest {$this->project} ";

        $command .= "-- --no-install --no-git --yes --skip-houston";

        $this->kernel->execute($command);
    }

    /**
     * Create a vite project and install its dependencies
     *
     * @param string $template [Project template]
     *
     * @return void
     */
    private function createViteProject(string $template): void
    {
        $type = $this->selectedTypes($this->input, $this->output, self::TYPES);

        $command = "cd resources/ && echo | npm create vite@latest {$this->project}";

        $command .= " -- --template {$template}" . ('js' === $type ? '' : '-ts');

        $this->kernel->execute($command);
    }

    /**
     * Create an electron-vite project and install its dependencies
     *
     * @param string $template [Project template]
     *
     * @return void
     */
    private function createElectronViteProject(string $template): void
    {
        $type = $this->selectedTypes($this->input, $this->output, self::TYPES);

        $command = "cd resources/ && echo | npm create @quick-start/electron";

        $command .= " {$this->project} -- --template {$template}" . ('js' === $type ? '' : '-ts') . ' --skip';

        $this->kernel->execute($command);
    }
}
