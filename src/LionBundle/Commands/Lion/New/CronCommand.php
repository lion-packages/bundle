<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a CRON class for scheduled tasks
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CronCommand extends Command
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

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CronCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): CronCommand
    {
        $this->store = $store;

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
     * @return int
     *
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $cron */
        $cron = $input->getArgument('cron');

        $this->classFactory->classFactory('app/Console/Cron/', $cron);

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $folder = $this->classFactory->getFolder();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use App\Console\Commands\ExampleCommand;
                use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
                use Lion\Bundle\Interface\ScheduleInterface;

                /**
                 * schedule {$class}
                 *
                 * @package {$namespace}
                 */
                class {$class} implements ScheduleInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public function schedule(Schedule \$schedule): void
                    {
                        \$schedule
                            ->cron('* * * * *')
                            ->command(ExampleCommand::class)
                            ->log('example');
                    }
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  CRON: {$class}"));

        $output->writeln($this->successOutput("\t>>  CRON: the '{$namespace}\\{$class}' cron has been generated"));

        return parent::SUCCESS;
    }
}
