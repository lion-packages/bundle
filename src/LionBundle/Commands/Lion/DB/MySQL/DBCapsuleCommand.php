<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB\MySQL;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate capsule classes with the properties of an entity
 *
 * @property ClassFactory $classFactory [Object of class ClassFactory]
 *
 * @package Lion\Bundle\Commands\Lion\DB\MySQL
 */
class DBCapsuleCommand extends MenuCommand
{
    /**
     * @var ClassFactory $classFactory [Object of class ClassFactory]
     */
    private ClassFactory $classFactory;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): DBCapsuleCommand
    {
        $this->classFactory = $classFactory;

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
            ->setName('db:mysql:capsule')
            ->setDescription('Command required for the creation of new Capsules')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name', null);
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
        $entity = $input->getArgument('entity');

        $selectedConnection = $this->selectConnectionByEnviroment($input, $output);

        $entity = $this->str->of($entity)->test("/-/") ? "`{$entity}`" : $entity;

        $columns = DB::connection($selectedConnection)->show()->columns()->from($entity)->getAll();

        if (!empty($columns->status)) {
            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$columns->message}"));

            return Command::FAILURE;
        }

        $properties = [];

        foreach ($columns as $column) {
            $properties[] = "{$column->Field}:{$this->classFactory->getDBType($column->Type)}";
        }

        $connPascal = $this->classFactory->getClassFormat($selectedConnection);

        $className = $this->classFactory->getClassFormat($this->str->of($entity)->replace('`', '')->get());

        $this->getApplication()
            ->find('new:capsule')
            ->run(
                new ArrayInput(['capsule' => "{$connPascal}/MySQL/{$className}", '--properties' => $properties]),
                $output
            );

        return Command::SUCCESS;
    }
}
