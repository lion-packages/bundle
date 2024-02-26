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
    const TEMPLATES = ['Vanilla', 'Vue', 'React', 'Preact', 'Lit', 'Svelte', 'Solid', 'Qwik'];
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
            ->setDescription(
                'Command to create Javascript projects with Vite.JS (Vanilla/Vue/React/Preact/Lit/Svelte/Solid/Qwik)'
            )
            ->addArgument('project', InputArgument::OPTIONAL, 'Project name', 'example');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$project = $this->str->of($input->getArgument('project'))->trim()->replace('_', '-')->replace(' ', '-')->get();

        if (isSuccess($this->store->exist("vite/{$project}/"))) {
            $output->writeln($this->warningOutput("\t>>  VITE: a resource with this name already exists"));

            return Command::FAILURE;
        }

        $template = $this->str->of($this->selectedTemplate($input, $output, self::TEMPLATES))->lower()->get();
        $type = $this->selectedTypes($input, $output, self::TYPES);
        $this->store->folder('./vite/');

        $commandCreate = "cd {$this->store->normalizePath('./vite/')} && echo | npm init vite@latest {$project}";
        $commandCreate .= " -- --template {$template}" . ('js' === $type ? '' : '-ts');

        $this->kernel->execute($commandCreate, false);
        $this->kernel->execute("cd {$this->store->normalizePath("./vite/{$project}/")} && npm install", false);

        $this->setViteConfig($project);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput(
            "\t>>  VITE: vite 'vite/{$project}/' project has been generated successfully"
        ));

		return Command::SUCCESS;
	}

    private function setViteConfig(string $project): void
    {
        $this->classFactory
            ->create('', 'env', "./vite/{$project}/")
            ->add('VITE_SERVER_URL="' . $_ENV['SERVER_URL_AUD'] . '"')
            ->close();

        $replace = [
            6 => [
                'replace' => true,
                'content' => ",\n  server: {\n    host: true,\n    port: 5173,\n    watch: {\n      usePolling: true\n    }\n  }",
                'search' => ','
            ]
        ];

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.js"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.js", $replace);
        }

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.ts"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.ts", $replace);
        }
    }
}
