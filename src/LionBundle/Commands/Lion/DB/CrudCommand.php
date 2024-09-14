<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a CRUD (Controllers, Models, Tests and Capsule Classes) of an entity
 *
 * @property FileWriter $fileWriter [Object of class FileWriter]
 * @property ClassFactory $classFactory [Object of class ClassFactory]
 * @property DatabaseEngine $databaseEngine [Manages basic database engine
 * processes]
 *
 * @package Lion\Bundle\Commands\Lion\DB
 */
class CrudCommand extends MenuCommand
{
    /**
     * [Defined CRUD methods]
     *
     * @const METHODS
     */
    private const array METHODS = [
        'create',
        'update',
        'delete',
    ];

    /**
     * [Object of class FileWriter]
     *
     * @var FileWriter $fileWriter
     */
    private FileWriter $fileWriter;

    /**
     * [Object of class ClassFactory]
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

    /**
     * @required
     */
    public function setFileWriter(FileWriter $fileWriter): CrudCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    /**
     * @required
     */
    public function setClassFactory(ClassFactory $classFactory): CrudCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     */
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): CrudCommand
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
            ->setName('db:crud')
            ->setDescription(
                'Command to generate controller and model of an entity with their respective CRUD functions'
            )
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

        $selectedConnection = $this->selectConnection($input, $output);

        $connectionName = Connection::getConnections()[$selectedConnection]['dbname'];

        $driver = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        $connectionPascal = $this->str->of($connectionName)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $entityPascal = $this->str->of($entity)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $columns = $this->getTableColumns($driver, $selectedConnection, $entity);

        if (isError($columns)) {
            $output->writeln($this->errorOutput("\t>>  CRUD: {$columns->message}"));

            return Command::FAILURE;
        }

        $this->addDBRules($entity, $output);

        $this->addControllerAndModel(
            $this->databaseEngine->getDriver($driver),
            $entityPascal,
            $connectionPascal,
            $entity,
            $columns,
            $output
        );

        $this->addCapsule(
            $this->databaseEngine->getDriver($driver),
            $entity,
            $connectionPascal,
            $entityPascal,
            $output
        );

        $output->writeln($this->infoOutput("\n\t>>  CRUD: crud has been generated for the '{$entity}' entity"));

        return Command::SUCCESS;
    }

    /**
     * Create the rules of an entity
     *
     * @param string $entity [Entity name]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function addDBRules(string $entity, OutputInterface $output): void
    {
        $this
            ->getApplication()
            ->find('db:rules')
            ->run(
                new ArrayInput([
                    'entity' => $entity,
                ]),
                $output
            );
    }

    /**
     * Create the controller and model of an entity
     *
     * @param string $driver [Database engine]
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param string $connectionPascal [Database name in pascal-case format]
     * @param string $entity [Entity name]
     * @param array $columns [List of defined columns]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function addControllerAndModel(
        string $driver,
        string $entityPascal,
        string $connectionPascal,
        string $entity,
        array $columns,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('new:controller')
            ->run(
                new ArrayInput([
                    'controller' => "{$connectionPascal}/{$driver}/{$entityPascal}Controller",
                    '--model' => "{$connectionPascal}/{$driver}/{$entityPascal}Model",
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput([
                    'test' => "App/Http/Controllers/{$connectionPascal}/{$driver}/{$entityPascal}ControllerTest",
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput([
                    'test' => "App/Models/{$connectionPascal}/{$driver}/{$entityPascal}ModelTest",
                ]),
                $output
            );

        $namespacePascal = "Database\\Class\\{$connectionPascal}\\{$driver}\\{$entityPascal}";

        $fileC = "{$entityPascal}Controller";

        $pathC = "app/Http/Controllers/{$connectionPascal}/{$driver}/{$fileC}.php";

        $this->fileWriter->readFileRows($pathC, [
            7 => [
                'replace' => true,
                'content' => ";\nuse {$namespacePascal};",
                'search' => ';',
            ],
            20 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            25 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            27 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . '->capsule())',
                'search' => '()'
            ],
            44 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            50 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            52 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . '->capsule())',
                'search' => '()'
            ],
            57 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            63 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            65 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . ')',
                'search' => '()'
            ]
        ]);

        $pathM = "app/Models/{$connectionPascal}/{$driver}/{$entityPascal}Model.php";

        $gettersCallModel = $this->generateCallGettersModel($entityPascal, $columns);

        if (Driver::MYSQL === $this->str->of($driver)->lower()->get()) {
            $listGettersCallModel = [
                'create' => '',
                'update' => '',
                'delete' => '',
            ];

            foreach ($gettersCallModel as $keyGetterCallModel => $method) {
                foreach ($method as $name) {
                    $listGettersCallModel[$keyGetterCallModel] .= $this->str
                        ->lt()->lt()->lt()->concat('$')->concat(lcfirst($entityPascal))->concat("->{$name}()")->concat(',')
                        ->ln()
                        ->get();
                }
            }

            $this->fileWriter->readFileRows($pathM, [
                6 => [
                    'replace' => false,
                    'content' => "\nuse {$namespacePascal};\n",
                ],
                20 => [
                    'replace' => true,
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                    'search' => '*',
                ],
                23 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                25 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'content' => "'create_{$entity}'",
                            'search' => "''",
                        ],
                        [
                            'content' => "[\n{$listGettersCallModel['create']}\t\t]",
                            'search' => '[]',
                        ],
                    ],
                ],
                36 => [
                    'replace' => true,
                    'content' => "'{$entity}'",
                    'search' => "''",
                ],
                43 => [
                    'replace' => true,
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                    'search' => '*',
                ],
                46 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                48 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'content' => "'update_{$entity}'",
                            'search' => "''",
                        ],
                        [
                            'content' => "[\n{$listGettersCallModel['update']}\t\t]",
                            'search' => '[]',
                        ],
                    ],
                ],
                54 => [
                    'replace' => true,
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                    'search' => '*',
                ],
                57 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                59 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'content' => "'delete_{$entity}'",
                            'search' => "''",
                        ],
                        [
                            'content' => "[\n{$listGettersCallModel['delete']}\t\t]",
                            'search' => '[]',
                        ],
                    ],
                ],
            ]);
        }

        if (Driver::POSTGRESQL === $this->str->of($driver)->lower()->get()) {
            $this->fileWriter->readFileRows($pathM, [
                6 => [
                    'replace' => false,
                    'content' => "\nuse {$namespacePascal};\n",
                ],
                7 => [
                    'replace' => true,
                    'search' => 'use Lion\Database\Drivers\MySQL as DB;',
                    'content' => 'use Lion\Database\Drivers\PostgreSQL as DB;',
                ],
                20 => [
                    'replace' => true,
                    'search' => '*',
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                ],
                23 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                25 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'search' => 'call',
                            'content' => 'query',
                        ],
                        [
                            'content' => '',
                            'search' => ', []',
                        ],
                    ],
                ],
                36 => [
                    'replace' => true,
                    'search' => 'table',
                    'content' => 'query',
                ],
                37 => [
                    'remove' => true,
                ],
                43 => [
                    'replace' => true,
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                    'search' => '*',
                ],
                46 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                48 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'search' => 'call',
                            'content' => 'query',
                        ],
                        [
                            'content' => "",
                            'search' => ', []',
                        ],
                    ],
                ],
                54 => [
                    'replace' => true,
                    'content' => (
                        "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                        "\t *"
                    ),
                    'search' => '*',
                ],
                57 => [
                    'replace' => true,
                    'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                    'search' => '()',
                ],
                59 => [
                    'replace' => true,
                    'multiple' => [
                        [
                            'search' => 'call',
                            'content' => 'query',
                        ],
                        [
                            'content' => "",
                            'search' => ', []',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Create an entity capsule
     *
     * @param string $driver [Database engine]
     * @param string $entity [Entity name]
     * @param string $connectionPascal [Database name in pascal-case format]
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function addCapsule(
        string $driver,
        string $entity,
        string $connectionPascal,
        string $entityPascal,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('db:capsule')
            ->run(
                new ArrayInput([
                    'entity' => $entity,
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput([
                    'test' => "Database/Class/{$connectionPascal}/{$driver}/{$entityPascal}Test",
                ]),
                $output
            );
    }

    /**
     * Generates the calls of the getter methods in the models, to add this
     * content to the generated model
     *
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param array $columns [List of defined columns]
     *
     * @return array
     */
    private function generateCallGettersModel(string $entityPascal, array $columns): array
    {
        $methods = ['create' => [], 'update' => [], 'delete' => []];

        foreach (self::METHODS as $method) {
            foreach ($columns as $column) {
                $getter = $this->classFactory->getProperty(
                    $column->Field,
                    $entityPascal,
                    $this->classFactory->getDBType($column->Type)
                );

                if ('create' === $method  && 'PRI' != $column->Key) {
                    $methods[$method][] = $getter->getter->name;
                }

                if ('update' === $method) {
                    $methods[$method][] = $getter->getter->name;
                }

                if ('delete' === $method && 'PRI' === $column->Key) {
                    $methods[$method][] = $getter->getter->name;
                }
            }
        }

        foreach ($columns as $key => $column) {
            if ('PRI' === $column->Key) {
                $methods['update'] = [
                    ...$this->arr->of($methods['update'])->where(fn ($value, $key) => $key != 0)->get(),
                    reset($methods['update'])
                ];
            }
        }

        return $methods;
    }
}
