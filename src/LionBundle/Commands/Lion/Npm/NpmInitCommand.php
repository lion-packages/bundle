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
 * @property FileWriter $fileWriter [FileWriter class object]
 * @property Kernel $Kernel [kernel class object]
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
     * [FileWriter class object]
     *
     * @property FileWriter $fileWriter
     */
    private FileWriter $fileWriter;

    /**
     * [Kernel class object]
     *
     * @property Kernel $kernel
     */
    private Kernel $kernel;

    #[Inject]
    public function setFileWriter(FileWriter $fileWriter): NpmInitCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

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
            ->addArgument('project', InputArgument::OPTIONAL, "Project's name", 'vite-project');
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

        if (isSuccess($this->store->exist("vite/{$project}/"))) {
            $output->writeln($this->warningOutput("\t>>  VITE: a resource with this name already exists"));

            return Command::FAILURE;
        }

        $this->store->folder('vite/');

        $template = $this->str
            ->of($this->selectedTemplate($input, $output, self::VITE_TEMPLATES, 'React', 2))->lower()
            ->get();

        if ('electron' === $template) {
            $electronTemplate = $this->str
                ->of($this->selectedTemplate($input, $output, self::VITE_ELECTRON_TEMPLATES, 'React', 2))->lower()
                ->get();

            $this->createElectronViteProject($input, $output, $project, $electronTemplate);

            $this->setViteConfig($project, 18, [
                'replace' => true,
                'content' => (
                    "],\n    server: {\n      host: true,\n      port: 5173,\n      watch: {\n        " .
                    "usePolling: true\n      }\n    }"
                ),
                'search' => ']'
            ]);
        } else {
            $this->createViteProject($input, $output, $project, $template);

            $this->setViteConfig($project, 6, [
                'replace' => true,
                'content' => (
                    ",\n  server: {\n    host: true,\n    port: 5173,\n    watch: {\n      " .
                    "usePolling: true\n    }\n  }"
                ),
                'search' => ','
            ]);
        }

        $output->writeln($this->warningOutput("\t>>  VITE: {$project}"));

        $output->writeln(
            $this->successOutput("\t>>  VITE: vite 'vite/{$project}/' project has been generated successfully")
        );

        $output->writeln($this->warningOutput("\t>>  VITE: add your configuration in the vite.config"));

        $output->writeln(
            $this->warningOutput("\t>>  VITE: server: { host: true, port: 5173, watch: { usePolling: true } }")
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

        $commandCreate = "cd vite/ && echo | npm create vite@latest {$project}";

        $commandCreate .= " -- --template {$template}" . ('js' === $type ? '' : '-ts');

        $this->kernel->execute($this->str->of($commandCreate)->trim()->get(), false);

        $this->kernel->execute("cd vite/{$project} && npm install", false);
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

        $commandCreate = "cd vite/ && echo | npm create @quick-start/electron";

        $commandCreate .= " {$project} -- --template {$template}" . ('js' === $type ? '' : '-ts') . ' --skip';

        $this->kernel->execute($commandCreate, false);

        $this->kernel->execute("cd vite/{$project} && npm install", false);
    }

    /**
     * Adds the configuration required to run a vite project in a Docker
     * environment
     *
     * @param string $project [Project's name]
     * @param int $rowNumber [File row number]
     * @param array $config [Settings to replace in configuration files]
     *
     * @return void
     */
    private function setViteConfig(string $project, int $rowNumber, array $config): void
    {
        $replace = [$rowNumber => $config];

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.js"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.js", $replace);
        }

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.ts"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.ts", $replace);
        }

        if (isSuccess($this->store->exist("vite/{$project}/electron.vite.config.mjs"))) {
            $this->fileWriter->readFileRows("vite/{$project}/electron.vite.config.mjs", $replace);
        }

        if (isSuccess($this->store->exist("vite/{$project}/electron.vite.config.ts"))) {
            $this->fileWriter->readFileRows("vite/{$project}/electron.vite.config.ts", $replace);
        }
    }
}
