<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RunQueuedTasksCommand description
 *
 * @property Container $container [Container to generate dependency injection]
 * @property TaskQueue $taskQueue [Manage server queued task processes]
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class RunQueuedTasksCommand extends MenuCommand
{
    /**
     * [Container to generate dependency injection]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Manage server queued task processes]
     *
     * @var TaskQueue $taskQueue
     */
    private TaskQueue $taskQueue;

    /**
     * @required
     */
    public function setContainer(Container $container): RunQueuedTasksCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     */
    public function setTaskQueue(TaskQueue $taskQueue): RunQueuedTasksCommand
    {
        $this->taskQueue = $taskQueue;

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
            ->setName('schedule:run')
            ->setDescription('Run queued tasks');
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
     * @return int [0 if everything went fine, or an exit code]
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $json = $this->taskQueue->get();

            if (NULL_VALUE === $json) {
                $output->writeln($this->infoOutput("\t>> SCHEDULE: no queued tasks available"));

                $this->taskQueue->pause(2);

                continue;
            }

            $queue = json_decode($json, true);

            $output->writeln(
                $this->warningOutput(
                    "\t>> SCHEDULE: {$queue['id']} / {$queue['namespace']}::{$queue['method']} [PROCESSING]"
                )
            );

            logger(
                "TASK: {$queue['id']}",
                LogTypeEnum::INFO,
                json_decode(json([
                    'class' => "{$queue['namespace']}::{$queue['method']}",
                    'params' => $queue['data'],
                    'return' => $this->container->injectDependenciesMethod(
                        new $queue['namespace'],
                        $queue['method'],
                        ['queue' => $queue, ...$queue['data']]
                    )
                ]), true)
            );

            $output->writeln(
                $this->successOutput(
                    "\t>> SCHEDULE: {$queue['id']} / {$queue['namespace']}::{$queue['method']} [COMPLETED]"
                )
            );
        }

        return Command::SUCCESS;
    }
}
