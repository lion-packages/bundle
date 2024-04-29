<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RunQueuedTasksCommand description
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class RunQueuedTasksCommand extends Command
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schedule:run')
            ->setDescription('Run queued tasks.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $data = DB::table('task_queue')
                ->select()
                ->getAll();

            if (isSuccess($data)) {
                $output->writeln($this->infoOutput("\t>> SCHEDULE: no queued tasks available"));

                TaskQueue::pause(60);

                continue;
            }

            foreach ($data as $queue) {
                $data = (object) json_decode($queue->task_queue_data, true);

                $output->writeln($this->warningOutput("\t>> SCHEDULE: {$queue->task_queue_type} [PROCESSING]"));

                if (TaskStatusEnum::PENDING->value === $queue->task_queue_status) {
                    $output->writeln($this->successOutput("\t>> SCHEDULE: {$queue->task_queue_type} [IN-PROGRESS]"));

                    TaskQueue::edit($queue, TaskStatusEnum::IN_PROGRESS);

                    TaskQueue::pause(1);

                    continue;
                }

                if (TaskStatusEnum::IN_PROGRESS->value === $queue->task_queue_status) {
                    $callable = TaskQueue::get($queue->task_queue_type);

                    $callable($queue);

                    $output->writeln($this->successOutput("\t>> SCHEDULE: {$queue->task_queue_type} [COMPLETED]"));

                    TaskQueue::edit($queue, TaskStatusEnum::COMPLETED);

                    TaskQueue::pause(1);

                    continue;
                }

                if (TaskStatusEnum::COMPLETED->value === $queue->task_queue_status) {
                    $output->writeln($this->successOutput("\t>> SCHEDULE: {$queue->task_queue_type} [REMOVED]"));

                    TaskQueue::remove($queue);

                    TaskQueue::pause(1);

                    continue;
                }
            }
        }

        return Command::SUCCESS;
    }
}
