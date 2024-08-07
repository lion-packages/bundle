<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Connection;
use stdClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates the base rules for the properties of an entity
 *
 * @property FileWriter $fileWrite [FileWriter class object]
 * @property DatabaseEngine $databaseEngine [Manages basic database engine
 * processes]
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class RulesDBCommand extends MenuCommand
{
    /**
     * [FileWriter class object]
     *
     * @var FileWriter $fileWrite
     */
    private FileWriter $fileWriter;

    /**
     * [Manages basic database engine processes]
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    /**
     * @required
     */
    public function setFileWriter(FileWriter $fileWriter): RulesDBCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    /**
     * @required
     */
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
     * @return int [0 if everything went fine, or an exit code]
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');

        $selectedConnection = $this->selectConnectionByEnviroment($input, $output);

        $connectionName = Connection::getConnections()[$selectedConnection]['dbname'];

        $databaseEngineType = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        $driver = $this->databaseEngine->getDriver($databaseEngineType);

        $entityPascal = $this->str->of($entity)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $connectionPascal = $this->str->of($connectionName)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $columns = $this->getTableColumns($databaseEngineType, $selectedConnection, $entity);

        if (isset($columns->status)) {
            $output->writeln($this->errorOutput($columns->message));

            return Command::FAILURE;
        }

        $foreigns = $this->getTableForeigns($databaseEngineType, $selectedConnection, $entity);

        foreach ($columns as $column) {
            $isForeign = false;

            if (!isset($foreigns->status)) {
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
                $output->writeln(
                    $this->infoOutput(
                        "\t>>  RULE: the rule for '{$column->Field}' property has been omitted, it is a foreign"
                    )
                );
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Generate rules for an entity
     *
     * @param string $connectionPascal [Connection name in PascalCase format]
     * @param string $entityPascal [Entity name in PascalCase format]
     * @param stdClass $column [Property object]
     * @param string $type [Defines whether the rule type is optional or
     * required]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function generateRule(
        string $driver,
        string $connectionPascal,
        string $entityPascal,
        stdClass $column,
        string $type,
        OutputInterface $output
    ): void {
        $ruleName = $this->str
            ->of($column->Field)
            ->replace('-', '_')
            ->replace('_', ' ')
            ->trim()
            ->pascal()
            ->concat($type)
            ->concat('Rule')
            ->get();

        $this
            ->getApplication()
            ->find('new:rule')
            ->run(
                new ArrayInput([
                    'rule' => "{$connectionPascal}/{$driver}/{$entityPascal}/{$ruleName}",
                ]),
                $output
            );

        $this->fileWriter->readFileRows("app/Rules/{$connectionPascal}/{$driver}/{$entityPascal}/{$ruleName}.php", [
            12 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            14 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            15 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            16 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            25 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            29 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            32 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            39 => [
                'replace' => true,
                'content' => "'" . strtolower($column->Field) . "'",
                'search' => "''"
            ],
            36 => [
                'replace' => true,
                'content' => "'{$column->Comment}'",
                'search' => "''"
            ],
            43 => [
                'replace' => true,
                'search' => '""',
                'content' => "'{$column->Default}'",
            ],
            50 => [
                'replace' => true,
                'content' => ($type === 'Required' ? 'false' : ($type === 'Optional' ? 'true' : 'false')),
                'search' => 'false'
            ],
            59 => [
                'replace' => true,
                'content' => ($type === 'Required' ? 'required' : ($type === 'Optional' ? 'optional' : 'required')),
                'search' => 'required'
            ],
            60 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => (
                            $type === 'Required' ? 'required' : ($type === 'Optional' ? 'optional' : 'required')
                        ),
                        'search' => 'required'
                    ],
                    [
                        'content' => '"' . strtolower($column->Field) . '"',
                        'search' => '""'
                    ]
                ]
            ]
        ]);
    }
}
