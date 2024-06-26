<?php

declare(strict_types=1);

use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Mailer\Mailer;
use Lion\Mailer\Priority;

/**
 * -----------------------------------------------------------------------------
 * Queued Tasks
 * -----------------------------------------------------------------------------
 * This is where you can register the processes required for your queued tasks
 * -----------------------------------------------------------------------------
 **/

TaskQueue::add('example', [Env::class, 'get']);

TaskQueue::add(
    'send:email:verify',
    (
        /**
         * Send an email configured in a task queue
         *
         * @param FileWriter $fileWriter [Class that allows writing system
         * files]
         * @param stdClass $queue [Queued task object]
         * @param string $template [HTML Template]
         * @param string $email [Email]
         *
         * @return void
         *
         * @throws Exception [Catch an exception if the email has not been sent]
         */
        function (FileWriter $fileWriter, stdClass $queue, string $template, string $email): void {
            try {
                Mailer::account(env('MAIL_NAME'))
                    ->subject('Test Priority')
                    ->from($email, 'Sleon')
                    ->addAddress('jjerez@dev.com', 'Jjerez')
                    ->body(str->of($template)->replace('{{ REPLACE_TEXT }}', $fileWriter::class)->get())
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
