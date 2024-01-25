<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmUninstallCommand extends Command
{
    private Kernel $kernel;
    private Store $store;
    private Str $str;
    private Arr $arr;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmUninstallCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): NpmUninstallCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): NpmUninstallCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * @required
     * */
    public function setArr(Arr $arr): NpmUninstallCommand
    {
        $this->arr = $arr;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:uninstall')
            ->setDescription('Command to uninstall dependencies with npm from a vite project')
            ->addArgument('packages', InputArgument::REQUIRED, 'Package name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $project = $this->selectedProject($input, $output);
        $pkg = $input->getArgument('packages');

        $cmd = $this->kernel->execute("cd vite/{$project}/ && npm uninstall {$pkg}", false);
        $output->writeln($this->arr->of($cmd)->join("\n"));

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
