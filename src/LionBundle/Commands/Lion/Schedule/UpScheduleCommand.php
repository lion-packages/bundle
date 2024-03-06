<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpScheduleCommand extends Command
{
    private ClassFactory $classFactory;
    private Container $container;
    private Store $store;
    private Kernel $kernel;
    private Str $str;

    /**
     * @required
     */
    public function setInject(
        ClassFactory $classFactory,
        Container $container,
        Store $store,
        Kernel $kernel,
        Str $str
    ): UpScheduleCommand
    {
        $this->classFactory = $classFactory;

        $this->container = $container;

        $this->store = $store;

        $this->kernel = $kernel;

        $this->str = $str;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('schedule:up')
            ->setDescription('Stores all scheduled tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('app/Console/Cron/'))) {
            $output->writeln($this->errorOutput("\t>> SCHEDULE: no scheduled tasks defined"));

            return Command::FAILURE;
        }

        /** @var array<ScheduleInterface> $files */
        $files = [];

        foreach ($this->container->getFiles('app/Console/Cron/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->container->getNamespace(
                    (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? str_replace('\\', '/', $file) : $file),
                    'App\\Console\\Cron\\',
                    $this->store->normalizePath('Cron/')
                );

                /** @var ScheduleInterface $cronClass */
                $cronClass = new $namespace();

                $files[] = $cronClass;
            }
        }

        /** @var array<string> $commands */
        $commands = [];

        foreach ($files as $scheduleInterface) {
            $schedule = new Schedule();

            $scheduleInterface->schedule($schedule);

            $config = $schedule->getConfig();

            $options = '';

            foreach ($config['options'] as $option => $value) {
                if ($this->str->of($option)->test('/-/')) {
                    $options .= "{$option} {$value} ";
                } else {
                    $options .= "{$value} ";
                }
            }

            /** @var Command $commandObject */
            $commandObject = new $config['command'];

            $output->writeln($this->warningOutput("\t>> SCHEDULE: {$config['command']}"));

            $command = "{$config['cron']} cd {$_ENV['CRONTAB_PROJECT_PATH']}";
            $command .= " && {$_ENV['CRONTAB_PHP_PATH']} {$_ENV['CRONTAB_PROJECT_PATH']}lion {$commandObject->getName()}";
            $command .= '' === $options ? '' : " {$options}";
            $command .= " >> {$_ENV['CRONTAB_PROJECT_PATH']}storage/logs/cron/{$config['logName']}.log 2>&1";

            $this->store->folder($this->store->normalizePath('./storage/logs/cron/'));

            $this->classFactory
                ->create(
                    $config['logName'],
                    ClassFactory::LOG_EXTENSION,
                    $this->store->normalizePath('./storage/logs/cron/')
                )
                ->close();

            $this->classFactory->classFactory(
                $this->store->normalizePath('./storage/logs/cron/'),
                $config['logName']
            );

            $commands[] = trim($command);
        }

        foreach ($commands as $command) {
            $crontabOutput = '(echo "' . $command . '") | crontab -';

            $this->kernel->execute($crontabOutput, false);

            $output->writeln($this->warningOutput("\t>> SCHEDULE: {$command}"));
        }

        return Command::SUCCESS;
    }
}
