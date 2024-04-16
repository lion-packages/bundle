<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Schedule;

use Exception;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExampleCommand description
 *
 * @package App\Console\Commands
 */
class ScheduleSchemaCommand extends MenuCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schedule:schema')
            ->setDescription('Create the necessary tables in the database to process the queued tasks');
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
    }

    /**
     * Interacts with the user
     *
     * This method is executed before the InputDefinition is validated
     *
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
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
        $connection = $this->selectConnection($input, $output);

        $this->createScheduleTable($connection);

        $output->writeln($this->successOutput("\t>> SCHEDULE: the schema for queued tasks has been created"));

        return Command::SUCCESS;
    }

    /**
     * Generate the schema for the queued tasks
     *
     * @param string $connection [Connection name where the table is created]
     *
     * @return void
     */
    private function createScheduleTable(string $connection): void
    {
        $response = Schema::connection($connection)
            ->createTable('task_queue', function () {
                Schema::int('idtask_queue')->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('task_queue_type', 255)->notNull();
                Schema::json('task_queue_data')->notNull();

                Schema::enum('task_queue_options', TaskStatusEnum::values())
                    ->notNull()
                    ->default(TaskStatusEnum::PENDING->value);

                Schema::int('task_queue_attempts', 11)->notNull();
                Schema::timeStamp('task_queue_create_at')->default(MySQLConstants::CURRENT_TIMESTAMP);
            })
            ->execute();

        if (isError($response)) {
            throw new Exception($response->message);
        }
    }
}
