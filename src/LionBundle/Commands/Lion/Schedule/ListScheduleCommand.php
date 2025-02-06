<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Displays a table with available scheduled tasks
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class ListScheduleCommand extends Command
{
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

    #[Inject]
    public function setStore(Store $store): ListScheduleCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
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
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('app/Console/Cron/'))) {
            $output->writeln($this->errorOutput("\t>> SCHEDULE: no scheduled tasks defined"));

            return parent::FAILURE;
        }

        /** @var array<int, array<int, string>|TableSeparator> $rows */
        $rows = [];

        /** @var non-empty-string $crontPath */
        $crontPath = $this->store->normalizePath('Cron/');

        $size = 0;

        foreach ($this->store->getFiles('app/Console/Cron/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->store->getNamespaceFromFile(
                    $this->store->normalizePath($file),
                    'App\\Console\\Cron\\',
                    $crontPath
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
                $command = new $config['command']();

                /** @var string $commandName */
                $commandName = $command->getName();

                $rows[] = [
                    $this->errorOutput($config['cron']),
                    $this->warningOutput($commandName),
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
            ->setFooterTitle("<info> Showing [{$size}] available scheduled tasks </info>")
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
