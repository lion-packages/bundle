<?php

declare(strict_types=1);

namespace Tests\Providers\Commands;

use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Request\Status;

trait TaskQueueSchemaProviderTrait
{
    private function createTaskQueueSchema(): void
    {
        $response = Schema::createTable('task_queue', function () {
            Schema::int('idtask_queue')->notNull()->autoIncrement()->primaryKey();
            Schema::varchar('task_queue_type', 255)->notNull();
            Schema::json('task_queue_data')->notNull();

            Schema::enum('task_queue_status', TaskStatusEnum::values())
                ->notNull()
                ->default(TaskStatusEnum::PENDING->value);

            Schema::int('task_queue_attempts', 11)->notNull();
            Schema::timeStamp('task_queue_create_at')->default(MySQLConstants::CURRENT_TIMESTAMP);
        })
            ->execute();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertSame(Status::SUCCESS, $response->status);
    }
}
