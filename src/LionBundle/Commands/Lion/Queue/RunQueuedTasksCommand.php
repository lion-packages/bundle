<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Queue;

use DI\Attribute\Inject;
use JsonException;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Queue\TaskQueue;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Dependency\Injection\Container;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Allows queued tasks to run in the background.
 */
class RunQueuedTasksCommand extends MenuCommand
{
    /**
     * Dependency Injection Container Wrapper.
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * Manage server queued task processes.
     *
     * @var TaskQueue $taskQueue
     */
    private TaskQueue $taskQueue;

    /**
     * Database in use.
     *
     * @var int $database
     */
    private int $database;

    #[Inject]
    public function setContainer(Container $container): RunQueuedTasksCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('queue:run')
            ->setDescription('Run queued tasks.')
            ->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Redis database, default value 0 for internal operations.', TaskQueue::LION_DATABASE) // phpcs:ignore
            ->addOption('pause', 'p', InputOption::VALUE_OPTIONAL, 'Defines the time to wait before retrieving tasks if all have been executed.', 60); // phpcs:ignore
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options.
     *
     * @param InputInterface $input InputInterface is the interface implemented
     * by all input classes.
     * @param OutputInterface $output OutputInterface is the interface
     * implemented by all Output classes.
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set
     * the code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented
     * by all input classes.
     * @param OutputInterface $output OutputInterface is the interface
     * implemented by all Output classes.
     *
     * @return int
     *
     * @throws JsonException If encoding to JSON fails.
     * @throws LogicException When this abstract method is not implemented.
     *
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $pause */
        $pause = $input->getOption('pause');

        /** @var string $database */
        $database = $input->getOption('database');

        $this->database = (int) $database;

        /** @var string $redisScheme */
        $redisScheme = env('REDIS_SCHEME');

        /** @var string $host */
        $host = env('REDIS_HOST');

        /** @var int $port */
        $port = env('REDIS_PORT');

        /** @var string $password */
        $password = env('REDIS_PASSWORD');

        $this->taskQueue = new TaskQueue(parameters: [
            TaskQueue::SCHEME => $redisScheme,
            TaskQueue::HOST => $host,
            TaskQueue::PORT => $port,
            TaskQueue::DATABASE => $this->database,
            TaskQueue::PARAMETERS => [
                TaskQueue::PASSWORD => $password,
            ],
        ]);

        /** @phpstan-ignore-next-line */
        while (true) {
            $json = $this->taskQueue->get();

            if (null === $json) {
                $output->writeln($this->infoOutput("\t>> TASK [DATABASE: {$this->database}]: There are no queued tasks available. [OMITTED]")); // phpcs:ignore

                $this->taskQueue->pause((int) $pause);

                continue;
            }

            /** @var array{
             *     id: string,
             *     namespace: string,
             *     method: string,
             *     data: array<string, mixed>
             * } $queue */
            $queue = json_decode($json, true);

            $output->writeln($this->warningOutput($this->getOutput('PROCESSING', $queue)));

            try {
                $instance = $this->container->resolve($queue['namespace']);

                $instanceParams = ['queue' => $queue, ...$queue['data']];

                $return = $this->container->callMethod($instance, $queue['method'], $instanceParams);

                if (is_object($return)) {
                    $return = (array) $return;
                }

                $log = [
                    'class' => "{$queue['namespace']}::{$queue['method']}",
                    'params' => $queue['data'],
                    'return' => $return,
                ];

                logger("TASK: {$queue['id']}", LogTypeEnum::INFO, $log);

                $output->writeln($this->successOutput($this->getOutput('COMPLETED', $queue)));
            } catch (Throwable $exception) {
                $loggerData = [
                    'class' => "{$queue['namespace']}::{$queue['method']}",
                    'params' => $queue['data'],
                    'error' => [
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTraceAsString(),
                    ],
                ];

                logger("TASK [DATABASE: {$this->database}]: {$queue['id']}", LogTypeEnum::ERROR, $loggerData);

                $output->writeln($this->errorOutput($this->getOutput('ERROR', $queue)));
            }
        }
    }

    /**
     * @param string $type
     * @param array{
     *     id: string,
     *     namespace: string,
     *     method: string,
     *     data: array<string, mixed>
     * } $queue
     *
     * @return string
     *
     */
    private function getOutput(string $type, array $queue): string
    {
        /** @var string $id */
        $id = $queue['id'];

        /** @var string $namespace */
        $namespace = $queue['namespace'];

        /** @var string $method */
        $method = $queue['method'];

        return "\t>> TASK [DATABASE: {$this->database}]: {$id} / {$namespace}::{$method} [{$type}]";
    }
}
