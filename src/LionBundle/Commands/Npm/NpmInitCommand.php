<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Bundle\Helpers\RedisClient;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmInitCommand extends Command
{
    const TEMPLATES = ['Vanilla', 'Vue', 'React', 'Preact', 'Lit', 'Svelte', 'Solid', 'Qwik'];
    const TYPES = ['js', 'ts'];

    private Store $store;
    private FileWriter $fileWriter;
    private Kernel $kernel;
    private Arr $arr;
    private Str $str;
    private ClassFactory $classFactory;
    private RedisClient $redisClient;

    /**
     * @required
     * */
    public function setInject(
        Store $store,
        FileWriter $fileWriter,
        Kernel $kernel,
        Arr $arr,
        Str $str,
        ClassFactory $classFactory,
        RedisClient $redisClient
    ): NpmInitCommand {
        $this->store = $store;
        $this->fileWriter = $fileWriter;
        $this->kernel = $kernel;
        $this->arr = $arr;
        $this->str = $str;
        $this->classFactory = $classFactory;
        $this->redisClient = $redisClient;

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
		$project = str->of($input->getArgument('project'))->trim()->replace('_', '-')->replace(' ', '-')->get();

        if (isSuccess($this->store->exist("vite/{$project}/"))) {
            $output->writeln($this->warningOutput("\t>>  VITE: a resource with this name already exists"));

            return Command::FAILURE;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $template = $this->str->of($this->selectedTemplate($input, $output, $helper))->lower()->get();
        $type = $this->selectedTypes($input, $output, $helper);

        $this->redisClient
            ->getClient()
            ->hmset('vite', [$project => json_encode(['template' => $template, 'type' => $type])]);

        $this->store->folder('./vite/');

        $this->kernel->execute(
            ("cd ./vite && echo | npm init vite {$project} -- --template {$template}" . ('js' === $type ? '' : '-ts')),
            false
        );

        $cmdOutput = $this->kernel->execute("cd ./vite/{$project}/ && npm install", false);

        $output->writeln($this->arr->of($cmdOutput)->join("\n"));

        $this->setViteConfig($project);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: vite '{$project}' project has been generated successfully"));

		return Command::SUCCESS;
	}

    private function selectedTemplate(InputInterface $input, OutputInterface $output, QuestionHelper $helper): string
    {
        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                'Select the type of template ' . $this->warningOutput('(default: React)'),
                self::TEMPLATES,
                2
            )
        );
    }

    private function selectedTypes(InputInterface $input, OutputInterface $output, QuestionHelper $helper): string
    {
        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                'Select type ' . $this->warningOutput('(default: js)'),
                self::TYPES,
                0
            )
        );
    }

    private function setViteConfig(string $project): void
    {
        $this->classFactory
            ->create('', 'env', "./vite/{$project}/")
            ->add('VITE_SERVER_URL="' . env->SERVER_URL_AUD . '"')
            ->close();

        $replace = [
            'replace' => true,
            'content' => ",\n  server: {\n    host: true,\n    port: 5173,\n    watch: {\n      usePolling: true\n    }\n  }",
            'search' => ","
        ];

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.js"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.js", [6 => $replace]);
        }

        if (isSuccess($this->store->exist("vite/{$project}/vite.config.ts"))) {
            $this->fileWriter->readFileRows("vite/{$project}/vite.config.ts", [6 => $replace]);
        }
    }
}
