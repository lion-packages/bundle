<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Npm;

use Lion\Bundle\Helpers\RedisClient;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmRunBuildCommand extends Command
{
    private RedisClient $redisClient;
    private Kernel $kernel;
    private Store $store;
    private Arr $arr;
    private Str $str;

    /**
     * @required
     * */
    public function setInject(
        RedisClient $redisClient,
        Kernel $kernel,
        Store $store,
        Arr $arr,
        Str $str
    ): NpmRunBuildCommand {
        $this->redisClient = $redisClient;
        $this->kernel = $kernel;
        $this->store = $store;
        $this->arr = $arr;
        $this->str = $str;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('npm:build')
            ->setDescription('Command to generate dist for a vite project');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $projects = $this->arr->of($this->redisClient->getClient()->hgetall('vite'))->keys()->get();
        $project = $this->selectedProject($input, $output, $projects);
        $this->kernel->execute("cd vite/{$project}/ && npm run build", false);

        $output->writeln($this->warningOutput("\n\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: project dist has been generated: ./vite/{$project}/"));

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
