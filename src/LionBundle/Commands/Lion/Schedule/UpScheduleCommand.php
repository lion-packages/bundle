<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
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
 * Add scheduled tasks in crontab
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class UpScheduleCommand extends Command
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): UpScheduleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): UpScheduleCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): UpScheduleCommand
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
            ->setName('schedule:up')
            ->setDescription('Stores all scheduled tasks');
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
     * @return int
     *
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('app/Console/Cron/'))) {
            $output->writeln($this->errorOutput("\t>> SCHEDULE: no scheduled tasks defined"));

            return parent::FAILURE;
        }

        /** @var array<int, ScheduleInterface> $files */
        $files = [];

        /** @var non-empty-string $cronPath */
        $cronPath = $this->store->normalizePath('Cron/');

        foreach ($this->store->getFiles('app/Console/Cron/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->store->getNamespaceFromFile(
                    $this->store->normalizePath($file),
                    'App\\Console\\Cron\\',
                    $cronPath
                );

                /** @var ScheduleInterface $cronClass */
                $cronClass = new $namespace();

                $files[] = $cronClass;
            }
        }

        if (empty($files)) {
            $output->writeln($this->infoOutput("\t>> SCHEDULE: No scheduled tasks available"));

            return parent::SUCCESS;
        }

        $data = [];

        foreach ($files as $scheduleInterface) {
            $schedule = new Schedule();

            $scheduleInterface->schedule($schedule);

            $config = $schedule->getConfig();

            if (empty($config['command'])) {
                $output->writeln(
                    $this->infoOutput("\t>> SCHEDULE: cron has not been configured '" . $scheduleInterface::class . "'")
                );

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

            /** @var Command $commandObject */
            $commandObject = new $config['command']();

            /** @var string $crontabProjectPath */
            $crontabProjectPath = env('CRONTAB_PROJECT_PATH');

            /** @var string $phpPath */
            $phpPath = env('CRONTAB_PHP_PATH');

            $command = "{$config['cron']} lion cd {$crontabProjectPath} && ";

            $command .= "{$phpPath} {$crontabProjectPath}lion {$commandObject->getName()}";

            $command .= '' === $options ? '' : " {$options}";

            $command .= " >> {$crontabProjectPath}storage/logs/cron/{$config['logName']}.log 2>&1";

            $data[] = [
                $config['command'],
                $this->infoOutput($command),
            ];

            $data[] = new TableSeparator();

            $this->store->folder($this->store->normalizePath('./storage/logs/cron/'));

            $exist = $this->store->exist($this->store->normalizePath('./storage/logs/cron/') . $config['logName']);

            if (isError($exist)) {
                $this->classFactory
                    ->create(
                        $config['logName'],
                        ClassFactory::LOG_EXTENSION,
                        $this->store->normalizePath('./storage/logs/cron/')
                    )
                    ->close();
            }
        }

        /** @var string $crontabPath */
        $crontabPath = env('CRONTAB_PATH');

        $output->writeln($this->warningOutput("\t>> CRONTAB PATH: {$crontabPath}crontab"));

        $output->writeln($this->infoOutput("\t>> CRON: sudo service cron status"));

        $output->writeln($this->infoOutput("\t>> CRON: sudo service cron stop"));

        $output->writeln($this->infoOutput("\t>> CRON: sudo service cron start"));

        new Table($output)
            ->setHeaderTitle($this->successOutput(' CRONTAB '))
            ->setHeaders(['CLASS', 'CRON'])
            ->setRows($data)
            ->render();

        return parent::SUCCESS;
    }
}
