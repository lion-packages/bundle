<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use DI\Attribute\Inject;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Dependency\Injection\Container;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Allows queued tasks to run in the background
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

    #[Inject]
    public function setContainer(Container $container): RunQueuedTasksCommand
    {
        $this->container = $container;

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
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        /** @var string $redisScheme */
        $redisScheme = env('REDIS_SCHEME');

        /** @var string $host */
        $host = env('REDIS_HOST');

        /** @var int $port */
        $port = env('REDIS_PORT');

        /** @var string $password */
        $password = env('REDIS_PASSWORD');

        $this->taskQueue = new TaskQueue([
            'scheme' => $redisScheme,
            'host' => $host,
            'port' => $port,
            'parameters' => [
                'password' => $password,
                'database' => TaskQueue::LION_DATABASE,
            ],
        ]);
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
     *
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @phpstan-ignore-next-line */
        while (true) {
            $json = $this->taskQueue->get();

            if (null === $json) {
                $output->writeln($this->infoOutput("\t>> SCHEDULE: no queued tasks available"));

                $this->taskQueue->pause(60);

                continue;
            }

            /** @var array{
             *     id: string,
             *     namespace: string,
             *     method: string,
             *     data: array<string, mixed>
             * } $queue */
            $queue = json_decode($json, true);

            $output->writeln(
                $this->warningOutput(
                    "\t>> SCHEDULE: {$queue['id']} / {$queue['namespace']}::{$queue['method']} [PROCESSING]"
                )
            );

            $json = [
                'class' => "{$queue['namespace']}::{$queue['method']}",
                'params' => $queue['data'],
                'return' => $this->container->callMethod(
                    $this->container->resolve($queue['namespace']),
                    $queue['method'],
                    [
                        'queue' => $queue,
                        ...$queue['data'],
                    ],
                ),
            ];

            logger("TASK: {$queue['id']}", LogTypeEnum::INFO, $json);

            $output->writeln(
                $this->successOutput(
                    "\t>> SCHEDULE: {$queue['id']} / {$queue['namespace']}::{$queue['method']} [COMPLETED]"
                )
            );
        }
    }
}
