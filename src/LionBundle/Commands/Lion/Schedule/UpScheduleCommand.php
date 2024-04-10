<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add scheduled tasks in crontab
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 * @property Kernel $kernel [Kernel class object]
 * @property Str $str [Str class object]
 * @property Arr $arr [Arr class object]
 *
 * @package Lion\Bundle\Commands\Lion\Schedule
 */
class UpScheduleCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

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
     * [Kernel class object]
     *
     * @var Kernel $kernel
     */
    private Kernel $kernel;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * @required
     */
    public function setInject(
        ClassFactory $classFactory,
        Container $container,
        Store $store,
        Kernel $kernel,
        Str $str,
        Arr $arr
    ): UpScheduleCommand {
        $this->classFactory = $classFactory;

        $this->container = $container;

        $this->store = $store;

        $this->kernel = $kernel;

        $this->str = $str;

        $this->arr = $arr;

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

        if (empty($files)) {
            $output->writeln($this->infoOutput("\t>> SCHEDULE: No scheduled tasks available"));

            return Command::SUCCESS;
        }

        /** @var array<string> $commands */
        $commands = [];

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

        $this->kernel->execute('(echo "' . $this->arr->of($commands)->join("\n") . '") | crontab -', false);

        return Command::SUCCESS;
    }
}
