<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;

    /**
     * @required
     */
    public function setClassFactory(ClassFactory $classFactory): CronCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setStore(Store $store): CronCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     */
    public function setStr(Str $str): CronCommand
    {
        $this->str = $str;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('new:cron')
            ->setDescription('Create a new scheduled task')
            ->addArgument('cron', InputArgument::OPTIONAL, 'Scheduled task name', 'ExampleCron');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cron = $input->getArgument('cron');

        $this->classFactory->classFactory($this->store->normalizePath('app/Cron/'), $cron);

        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();
        $folder = $this->classFactory->getFolder();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                $this->str
                    ->of('<?php')->ln()->ln()
                    ->concat('declare(strict_types=1);')->ln()->ln()
                    ->concat("namespace {$namespace};")->ln()->ln()
                    ->concat('use Lion\Bundle\Helpers\Commands\Schedule\Schedule;')->ln()
                    ->concat('use Lion\Bundle\Interface\ScheduleInterface;')->ln()->ln()
                    ->concat("/**\n * schedule {$class}\n *\n * @package {$namespace}\n * */")->ln()
                    ->concat("class {$class} implements ScheduleInterface")->ln()
                    ->concat('{')->ln()
                    ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                    ->lt()->concat('public function schedule(Schedule $schedule): void')->ln()
                    ->lt()->concat('{')->ln()
                    ->lt()->lt()->concat(
                        '$schedule->cron(' . "'* * * * *'" . ')->command(' . "''" . ')->log(' . "''" . ');'
                    )->ln()
                    ->lt()->concat('}')->ln()
                    ->concat('}')->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  CRON: {$class}"));
        $output->writeln($this->successOutput("\t>>  CRON: the '{$namespace}\\{$class}' cron has been generated"));

        return Command::SUCCESS;
    }
}
