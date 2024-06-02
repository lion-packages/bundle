<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use Lion\Bundle\Helpers\Commands\Schedule\Schedule;

/**
 * Deploy settings for scheduled tasks
 *
 * @package Lion\Bundle\Interface
 */
interface ScheduleInterface
{
    /**
     * Define settings for scheduled tasks
     *
     * @param Schedule $schedule [Configuration object for scheduled tasks]
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void;
}
