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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpScheduleCommand extends Command
{
    private ClassFactory $classFactory;
    private Container $container;
    private Store $store;
    private Kernel $kernel;

    /**
     * @required
     */
    public function setClassFactory(ClassFactory $classFactory): UpScheduleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setContainer(Container $container): UpScheduleCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): UpScheduleCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     */
    public function setKernel(Kernel $kernel): UpScheduleCommand
    {
        $this->kernel = $kernel;

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
        if (isError($this->store->exist('./app/Cron/'))) {
            $output->writeln($this->errorOutput("\t>> SCHEDULE: no scheduled tasks defined"));

            return Command::FAILURE;
        }

        /** @var array<ScheduleInterface> $files */
        $files = [];

        foreach ($this->container->getFiles('./app/Cron/') as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $namespace = $this->container->getNamespace(
                    (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? str_replace('\\', '/', $file) : $file),
                    'App\\Cron\\',
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
                $options .= "{$option} {$value}";
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
