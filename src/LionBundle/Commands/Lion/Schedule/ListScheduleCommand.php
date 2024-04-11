<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Displays a table with available scheduled tasks
 *
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class ListScheduleCommand extends Command
{
    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * @required
     */
    public function setContainer(Container $container): ListScheduleCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): ListScheduleCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     */
    public function setStr(Str $str): ListScheduleCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schedule:list')
            ->setDescription('Displays a list of available scheduled tasks');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
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
        if (isError($this->store->exist('app/Console/Cron/'))) {
            $output->writeln($this->errorOutput("\t>> SCHEDULE: no scheduled tasks defined"));

            return Command::FAILURE;
        }

        /** @var array<ScheduleInterface> $rows */
        $rows = [];

        $size = 0;

        foreach ($this->container->getFiles('app/Console/Cron/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->container->getNamespace(
                    (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? str_replace('\\', '/', $file) : $file),
                    'App\\Console\\Cron\\',
                    $this->store->normalizePath('Cron/')
                );

                /** @var ScheduleInterface $cronClass */
                $cronClass = new $namespace();

                $schedule = new Schedule();

                $cronClass->schedule($schedule);

                $config = $schedule->getConfig();

                if (empty($config['command'])) {
                    $output->writeln($this->infoOutput("\t>> SCHEDULE: cron has not been configured '{$namespace}'"));

                    continue;
                }

                $options = '';

                foreach ($config['options'] as $option => $value) {
                    if ($this->str->of($option)->test('/-/')) {
                        $options .= "{$option} {$value} ";
                    } else {
                        $options .= "{$value} ";
                    }
                }

                /** @var Command $command */
                $command = new $config['command'];

                $rows[] = [
                    $this->errorOutput($config['cron']),
                    $this->warningOutput($command->getName()),
                    $this->warningOutput('' === $options ? 'N/A' : $options),
                    $this->infoOutput($config['logName']),
                ];

                $rows[] = new TableSeparator();

                $size++;
            }
        }

        $output->writeln('');

        (new Table($output))
            ->setHeaderTitle('<info> SCHEDULED TASKS </info>')
            ->setHeaders(['CRON', 'COMMAND', 'OPTIONS', 'LOG'])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [{$size}] available scheduled tasks </info>"
                    : ($size === 1
                        ? '<info> Showing a scheduled task </info>'
                        : '<info> No scheduled tasks available </info>'
                    )
            )
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
