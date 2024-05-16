<?php

declare(strict_types=1);

namespace Tests\Enums;

use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Test\Test;

class TaskStatusEnumTest extends Test
{
    public function testValues(): void
    {
        $this->assertSame([
            TaskStatusEnum::PENDING->value,
            TaskStatusEnum::IN_PROGRESS->value,
            TaskStatusEnum::COMPLETED->value,
            TaskStatusEnum::FAILED->value
        ], TaskStatusEnum::values());
    }
}
