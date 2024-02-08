<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmUpdateCommand extends Command
{
    private Kernel $kernel;
    private Store $store;
    private Str $str;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmUpdateCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): NpmUpdateCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): NpmUpdateCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:update')
            ->setDescription('Command to install dependencies with npm for a vite project');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$project = $this->selectedProject($input, $output);
        $this->kernel->execute("cd vite/{$project}/ && npm update > /dev/null 2>&1 || npm update > nul 2>&1", false);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: dependencies have been updated"));

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
