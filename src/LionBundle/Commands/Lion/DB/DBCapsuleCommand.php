<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Connection;
use LogicException;
use stdClass;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate capsule classes with the properties of an entity
 *
 * @package Lion\Bundle\Commands\Lion\DB\MySQL
 */
class DBCapsuleCommand extends MenuCommand
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Manages basic database engine processes]
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): DBCapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): DBCapsuleCommand
    {
        $this->databaseEngine = $databaseEngine;

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
            ->setName('db:capsule')
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
     * @return int
     *
     * @throws ExceptionInterface
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $entity */
        $entity = $input->getArgument('entity');

        /** @var string $entity */
        $entity = $this->str->of($entity)->test("/-/") ? "`{$entity}`" : $entity;

        $selectedConnection = $this->selectConnectionByEnviroment($input, $output);

        $connections = Connection::getConnections();

        $connectionName = $connections[$selectedConnection]['dbname'];

        /** @var string $databaseEngineType */
        $databaseEngineType = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        $driver = $this->databaseEngine->getDriver($databaseEngineType);

        /** @var array<int, stdClass>|stdClass $columns */
        $columns = $this->getTableColumns($databaseEngineType, $selectedConnection, $entity);

        if (!empty($columns->status)) {
            /** @var string $message */
            $message = $columns->message;

            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$message}"));

            return parent::FAILURE;
        }

        if (is_array($columns)) {
            $properties = [];

            foreach ($columns as $column) {
                /** @var string $field */
                $field = $column->Field;

                /** @var string $type */
                $type = $column->Type;

                $properties[] = "{$field}:{$this->classFactory->getDBType($type)}";
            }

            $connPascal = $this->classFactory->getClassFormat($connectionName);

            /** @var string $formatEntity */
            $formatEntity = $this->str
                ->of($entity)
                ->replace('`', '')
                ->get();

            $className = $this->classFactory->getClassFormat($formatEntity);

            $arrayInput = new ArrayInput([
                'capsule' => "{$connPascal}/{$driver}/{$className}",
                '--properties' => $properties,
                '--entity' => $entity,
            ]);

            /** @phpstan-ignore-next-line */
            $this->getApplication()
                ->find('new:capsule')
                ->run($arrayInput, $output);
        }

        return parent::SUCCESS;
    }
}
