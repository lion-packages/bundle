<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

/**
 * Create settings for scheduled tasks
 *
 * @property string $cron [Define crontab configuration]
 * @property string $command [Command defined for execution]
 * @property array $options [Defines the command options and arguments for
 * execution]
 * @property string $logName [Defines the name of the log file to record the
 * outputs]
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class Schedule
{
    /**
     * [Define crontab configuration]
     *
     * @var string $cron
     */
    private string $cron;

    /**
     * [Command defined for execution]
     *
     * @var string $command
     */
    private string $command;

    /**
     * [Defines the command options and arguments for execution]
     *
     * @var array $options
     */
    private array $options;

    /**
     * [Defines the name of the log file to record the outputs]
     *
     * @var string $logName
     */
    private string $logName;

    /**
     * Define the configuration
     *
     * @param string $cron [Define the cron configuration for execution]
     *
     * @return Schedule
     */
    public function cron(string $cron): Schedule
    {
        $this->cron = $cron;

        return $this;
    }

    /**
     * Define the command
     *
     * @param string $command [Command defined for execution]
     * @param array $options [Defines the command options and arguments for
     * execution]
     *
     * @return Schedule
     */
    public function command(string $command, array $options = []): Schedule
    {
        $this->command = $command;

        $this->options = $options;

        return $this;
    }

    /**
     * Defines the log record
     *
     * @param string $logName [Defines the name of the log file to record the
     * outputs]
     *
     * @return Schedule
     */
    public function log(string $logName): Schedule
    {
        $this->logName = $logName;

        return $this;
    }

    /**
     * Returns the configuration of the scheduled task
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'cron' => $this->cron,
            'command' => $this->command,
            'options' => $this->options,
            'logName' => $this->logName,
        ];
    }
}
