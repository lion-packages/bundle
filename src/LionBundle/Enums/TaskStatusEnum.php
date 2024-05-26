<?php

declare(strict_types=1);

namespace Lion\Bundle\Enums;

/**
 * Enabled states for queued tasks
 *
 * @package Lion\Bundle\Enums
 */
enum TaskStatusEnum: string
{
    /**
     * [Defines a task in the PENDING state]
     */
    case PENDING = 'PENDING';

    /**
     * [Defines a task in the IN_PROGRESS state]
     */
    case IN_PROGRESS = 'IN-PROGRESS';

    /**
     * [Defines a task in the COMPLETED state]
     */
    case COMPLETED = 'COMPLETED';

    /**
     * [Defines a task in the FAILED state]
     */
    case FAILED = 'FAILED';

    /**
     * Return a list with the different types of status available
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (object $value) => $value->value, self::cases());
    }
}
