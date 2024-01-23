<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Helpers\RedisClient;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NpmRunBuildCommand extends Command
{
    private RedisClient $redisClient;
    private Arr $arr;
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setRedisClient(RedisClient $redisClient): NpmRunBuildCommand
    {
        $this->redisClient = $redisClient;

        return $this;
    }

    /**
     * @required
     * */
    public function setArr(Arr $arr): NpmRunBuildCommand
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): NpmRunBuildCommand
    {
        $this->kernel = $kernel;

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

        $cmdOutput = $this->kernel->execute("cd vite/{$project}/ && npm run build", false);
        $output->writeln($this->arr->of($cmdOutput)->join("\n"));

		return Command::SUCCESS;
	}

    private function selectedProject(InputInterface $input, OutputInterface $output, array $projects): string
    {
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
