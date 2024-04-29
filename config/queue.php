<?php

declare(strict_types=1);

use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Mailer\Mailer;
use Lion\Mailer\Priority;

/**
 * -----------------------------------------------------------------------------
 * Queued Tasks
 * -----------------------------------------------------------------------------
 * This is where you can register the processes required for your queued tasks
 * -----------------------------------------------------------------------------
 **/

TaskQueue::add(
    'send:email:verify',
    (
        /**
         * Send an email configured in a task queue
         *
         * @param object $queue [Queued task object]
         *
         * @return void
         *
         * @throws Exception [Catch an exception if the email has not been sent]
         */
        function (object $queue): void {
            $data = (object) json_decode($queue->task_queue_data, true);

            try {
                Mailer::account(env('MAIL_NAME'))
                    ->subject('Test Priority')
                    ->from($data->email, 'Sleon')
                    ->addAddress('jjerez@dev.com', 'Jjerez')
                    ->body($data->template)
                    ->priority(Priority::HIGH)
                    ->send();
            } catch (Exception $e) {
                TaskQueue::edit($queue, TaskStatusEnum::FAILED);

                logger($e->getMessage(), LogTypeEnum::ERROR, [
                    'idtask_queue' => $queue->idtask_queue,
                    'task_queue_type' => $queue->task_queue_type,
                    'task_queue_data' => $queue->task_queue_data
                ]);
            }
        }
    )
);
