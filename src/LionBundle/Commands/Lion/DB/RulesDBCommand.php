<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Database\Connection;
use LogicException;
use stdClass;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates the base rules for the properties of an entity
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class RulesDBCommand extends MenuCommand
{
    /**
     * Class that allows writing system files
     *
     * @var FileWriter $fileWrite
     */
    private FileWriter $fileWriter;

    /**
     * Manages basic database engine processes
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    #[Inject]
    public function setFileWriter(FileWriter $fileWriter): RulesDBCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): RulesDBCommand
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
            ->setName('db:rules')
            ->setDescription('Command to generate the rules of an entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return int
     *
     * @throws ExceptionInterface
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $entity */
        $entity = $input->getArgument('entity');

        $selectedConnection = $this->selectConnectionByEnviroment($input, $output);

        $databaseName = Connection::getConnections()[$selectedConnection]['dbname'];

        /** @var string $databaseEngineType */
        $databaseEngineType = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        $driver = $this->databaseEngine->getDriver($databaseEngineType);

        /** @var string $entityPascal */
        $entityPascal = $this->str
            ->of($entity)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->pascal()
            ->get();

        /** @var string $connectionPascal */
        $connectionPascal = $this->str
            ->of($databaseName)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->pascal()
            ->get();

        /** @var array<int, stdClass>|stdClass $columns */
        $columns = $this->getTableColumns($databaseEngineType, $selectedConnection, $entity);

        if (isset($columns->status)) {
            /** @phpstan-ignore-next-line */
            $output->writeln($this->errorOutput($columns->message));

            return parent::FAILURE;
        }

        /** @var array<int, stdClass>|stdClass $foreigns */
        $foreigns = $this->getTableForeigns($databaseEngineType, $selectedConnection, $databaseName, $entity);

        if (is_array($columns) && !empty($columns)) {
            foreach ($columns as $column) {
                $isForeign = false;

                if (is_array($foreigns) && !empty($foreigns)) {
                    foreach ($foreigns as $foreign) {
                        if ($column->Field === $foreign->COLUMN_NAME) {
                            $isForeign = true;
                        }
                    }
                }

                if (!$isForeign) {
                    if ($column->Null === 'YES') {
                        $this->generateRule(
                            $driver,
                            $connectionPascal,
                            $entityPascal,
                            $column,
                            'Optional',
                            $output
                        );

                        $this->generateRule(
                            $driver,
                            $connectionPascal,
                            $entityPascal,
                            $column,
                            'Required',
                            $output
                        );
                    } else {
                        $this->generateRule(
                            $driver,
                            $connectionPascal,
                            $entityPascal,
                            $column,
                            '',
                            $output
                        );
                    }
                } else {
                    /** @var string $field */
                    $field = $column->Field;

                    $output->writeln(
                        $this->infoOutput(
                            "\t>>  RULE: The rule for property '{$field}' has been omitted, it is an external property."
                        )
                    );
                }
            }
        }

        return parent::SUCCESS;
    }

    /**
     * Generate rules for an entity
     *
     * @param string $driver Database Engine Type
     * @param string $connectionPascal Connection name in PascalCase format
     * @param string $entityPascal Entity name in PascalCase format
     * @param stdClass $column Property object
     * @param string $type Defines whether the rule type is optional or
     * required
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return void
     *
     * @throws ExceptionInterface When input binding fails. Bypass this by calling
     * ignoreValidationErrors()
     */
    private function generateRule(
        string $driver,
        string $connectionPascal,
        string $entityPascal,
        stdClass $column,
        string $type,
        OutputInterface $output
    ): void {
        /** @var string $field */
        $field = $column->Field;

        /** @var string $ruleName */
        $ruleName = $this->str
            ->of($field)
            ->replace('-', '_')
            ->replace('_', ' ')
            ->trim()
            ->pascal()
            ->concat($type)
            ->concat('Rule')
            ->get();

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('new:rule')
            ->run(
                new ArrayInput([
                    'rule' => "{$connectionPascal}/{$driver}/{$entityPascal}/{$ruleName}",
                    '--field' => $field,
                ]),
                $output
            );

        $this->fileWriter->readFileRows("app/Rules/{$connectionPascal}/{$driver}/{$entityPascal}/{$ruleName}.php", [
            30 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => (
                            $type === 'Required' ? 'required' : ($type === 'Optional' ? 'optional' : 'required')
                        ),
                        'search' => 'required',
                    ],
                ],
            ],
            31 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => (
                            $type === 'Required' ? 'required' : ($type === 'Optional' ? 'optional' : 'required')
                        ),
                        'search' => 'required',
                    ],
                ],
            ],
        ]);
    }
}
