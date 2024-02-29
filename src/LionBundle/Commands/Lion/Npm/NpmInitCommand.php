<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NpmInitCommand extends MenuCommand
{
    /**
     * [List of templates available to create electron-vite projects]
     *
     * @const VITE_ELECTRON_TEMPLATES
     */
    const VITE_ELECTRON_TEMPLATES = ['Vanilla', 'Vue', 'React', 'Svelte', 'Solid'];

    /**
     * [List of templates available to create vite projects]
     *
     * @const VITE_TEMPLATES
     */
    const VITE_TEMPLATES = ['Vanilla', 'Vue', 'React', 'Preact', 'Lit', 'Svelte', 'Solid', 'Qwik', 'Electron'];

    /**
     * [List of languages available to create a project]
     *
     * @const TYPES
     */
    const TYPES = ['js', 'ts'];

    private ClassFactory $classFactory;
    private FileWriter $fileWriter;
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): NpmInitCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setFileWriter(FileWriter $fileWriter): NpmInitCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmInitCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('npm:init')
            ->setDescription('Command to create Javascript projects with Vite.JS (Vanilla/Vue/React/Preact/Lit/Svelte/Solid/Qwik/Electron)')
            ->addArgument('project', InputArgument::OPTIONAL, "Project's name", 'vite-project');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$project = $this->str->of($input->getArgument('project'))->trim()->replace('_', '-')->replace(' ', '-')->get();

        if (isSuccess($this->store->exist($this->store->normalizePath("vite/{$project}/")))) {
            $output->writeln($this->warningOutput("\t>>  VITE: a resource with this name already exists"));

            return Command::FAILURE;
        }

        $this->store->folder($this->store->normalizePath('./vite/'));

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
                'content' => "],\n    server: {\n      host: true,\n      port: 5173,\n      watch: {\n        usePolling: true\n      }\n    }",
                'search' => ']'
            ]);
        } else {
            $this->createViteProject($input, $output, $project, $template);

            $this->setViteConfig($project, 6, [
                'replace' => true,
                'content' => ",\n  server: {\n    host: true,\n    port: 5173,\n    watch: {\n      usePolling: true\n    }\n  }",
                'search' => ','
            ]);
        }

        $output->writeln($this->warningOutput("\t>>  VITE: {$project}"));

        $output->writeln(
            $this->successOutput(
                "\t>>  VITE: vite '{$this->store->normalizePath("vite/{$project}/")}' project has been generated successfully"
            )
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
     * @param  InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param  OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param  string $project [Project's name]
     * @param  string $template [Project template]
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

        $commandCreate = "cd {$this->store->normalizePath('./vite/')} && echo | npm init vite@latest {$project}";
        $commandCreate .= " -- --template {$template}" . ('js' === $type ? '' : '-ts');

        $this->kernel->execute($commandCreate, false);
        $this->kernel->execute("cd {$this->store->normalizePath("./vite/{$project}/")} && npm install", false);
    }

    /**
     * Create an electron-vite project and install its dependencies
     *
     * @param  InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param  OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param  string $project [Project's name]
     * @param  string $template [Project template]
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

        $commandCreate = "cd {$this->store->normalizePath('./vite/')} && echo | npm create @quick-start/electron";
        $commandCreate .= " {$project} -- --template {$template}" . ('js' === $type ? '' : '-ts') . ' --skip';

        $this->kernel->execute($commandCreate, false);
        $this->kernel->execute("cd {$this->store->normalizePath("./vite/{$project}/")} && npm install", false);
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
        $this->classFactory
            ->create('', 'env', $this->store->normalizePath("./vite/{$project}/"))
            ->add('VITE_SERVER_URL="' . $_ENV['SERVER_URL_AUD'] . '"' . "\n")
            ->add('VITE_SERVER_URL_AUD="' . $_ENV['SERVER_URL'] . '"')
            ->close();

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
