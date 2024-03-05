<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

/**
 * Create settings for scheduled tasks
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class Schedule
{
    /**
     * [Defines the crontab path]
     *
     * @var string $crontabPath
     */
    private string $crontabPath;

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
     * Class constructor
     */
    public function __construct()
    {
        $this->crontabPath = $_ENV['CRONTAB_PATH'];
    }

    /**
     * Define the configuration
     *
     * @param  string $cron [Define the cron configuration for execution]
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
     * @param  string $command [Command defined for execution]
     * @param  array $options [Defines the command options and arguments for
     * execution]
     *
     * @return void
     */
    public function command(string $command, array $options = []): void
    {
        $this->command = $command;

        $this->options = $options;
    }

    /**
     * Returns the configuration of the scheduled task
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'crontabPath' => $this->crontabPath,
            'cron' => $this->cron,
            'command' => $this->command,
            'options' => $this->options,
        ];
    }
}
