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

/**
 * Generate a CRON class for scheduled tasks
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CronCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

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

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new:cron')
            ->setDescription('Command required to create a new scheduled task')
            ->addArgument('cron', InputArgument::OPTIONAL, 'Scheduled task name', 'ExampleCron');
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
        $cron = $input->getArgument('cron');

        $this->classFactory->classFactory('app/Console/Cron/', $cron);

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
