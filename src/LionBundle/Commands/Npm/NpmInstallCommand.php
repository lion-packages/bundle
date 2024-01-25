<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmInstallCommand extends Command
{
    private Str $str;
    private Kernel $kernel;
    private Store $store;

    /**
     * @required
     * */
    public function setStr(Str $str): NpmInstallCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmInstallCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): NpmInstallCommand
    {
        $this->store = $store;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:install')
            ->setDescription('Command to install dependencies with npm for a certain vite project')
            ->addArgument('packages', InputArgument::OPTIONAL, 'Package name', '');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);

		$cmd = $this->kernel->execute("cd vite/{$project}/ && npm install {$input->getArgument('packages')}", false);
        $output->writeln(arr->of($cmd)->join("\n"));

		return Command::SUCCESS;
	}

    private function selectedProject(InputInterface $input, OutputInterface $output): string
    {
        $projects = [];

        foreach ($this->store->view('./vite/') as $folder) {
            $split = $this->str->of($folder)->split('vite/');
            $projects[] = end($split);
        }

        if (count($projects) <= 1) {
            $output->writeln($this->warningOutput('(default: ' . reset($projects) . ')'));

            return reset($projects);
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select project ' . $this->warningOutput('(default: ' . reset($projects) . ')')),
                $projects,
                0
            )
        );
    }
}
