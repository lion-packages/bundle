<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Database\Connection;
use Lion\Database\Driver;
use LogicException;
use stdClass;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a CRUD (Controllers, Models, Tests and Capsule Classes) of an entity
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
     * [Class that allows writing system files]
     *
     * @var FileWriter $fileWriter
     */
    private FileWriter $fileWriter;

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
    public function setFileWriter(FileWriter $fileWriter): CrudCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CrudCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
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
     * @return int
     *
     * @throws ExceptionInterface
     * @throws LogicException [When this abstract method is not implemented]
     *
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $entity */
        $entity = $input->getArgument('entity');

        $selectedConnection = $this->selectConnection($input, $output);

        $connectionName = Connection::getConnections()[$selectedConnection]['dbname'];

        /** @var string $driver */
        $driver = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        /** @var string $connectionPascal */
        $connectionPascal = $this->str
            ->of($connectionName)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->pascal()
            ->get();

        /** @var string $entityPascal */
        $entityPascal = $this->str
            ->of($entity)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->pascal()
            ->get();

        /** @var array<int, stdClass>|stdClass $columns */
        $columns = $this->getTableColumns($driver, $selectedConnection, $entity);

        if (is_object($columns) && isError($columns)) {
            /** @var string $message */
            $message = $columns->message;

            $output->writeln($this->errorOutput("\t>>  CRUD: {$message}"));

            return parent::FAILURE;
        }

        if (is_array($columns)) {
            $this->addDBRules($entity);

            $this->addControllerAndModel(
                $this->databaseEngine->getDriver($driver),
                $entityPascal,
                $connectionPascal,
                $entity,
                $columns
            );

            $this->addCapsule(
                $this->databaseEngine->getDriver($driver),
                $entity,
                $connectionPascal,
                $entityPascal,
                $output
            );

            $output->writeln($this->infoOutput("\n\t>>  CRUD: crud has been generated for the '{$entity}' entity"));
        }

        return parent::SUCCESS;
    }

    /**
     * Create the rules of an entity
     *
     * @param string $entity [Entity name]
     *
     * @return void
     *
     * @throws ExceptionInterface
     *
     * @codeCoverageIgnore
     */
    private function addDBRules(string $entity): void
    {
        $arrayInput = new ArrayInput([
            'entity' => $entity,
        ]);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('db:rules')
            ->run($arrayInput, $this->output);
    }

    /**
     * Create the controller and model of an entity
     *
     * @param string $driver [Database engine]
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param string $connectionPascal [Database name in pascal-case format]
     * @param string $entity [Entity name]
     * @param array<int, stdClass> $columns [List of defined columns]
     *
     * @return void
     *
     * @throws ExceptionInterface
     *
     * @codeCoverageIgnore
     */
    private function addControllerAndModel(
        string $driver,
        string $entityPascal,
        string $connectionPascal,
        string $entity,
        array $columns
    ): void {
        $arrayInputController = new ArrayInput([
            'controller' => "{$connectionPascal}/{$driver}/{$entityPascal}Controller",
            '--model' => "{$connectionPascal}/{$driver}/{$entityPascal}Model",
        ]);

        $arrayInputModel = new ArrayInput([
            'test' => "App/Http/Controllers/{$connectionPascal}/{$driver}/{$entityPascal}ControllerTest",
        ]);

        $arrayInputTest = new ArrayInput([
            'test' => "App/Models/{$connectionPascal}/{$driver}/{$entityPascal}ModelTest",
        ]);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('new:controller')
            ->run($arrayInputController, $this->output);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('new:test')
            ->run($arrayInputModel, $this->output);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('new:test')
            ->run($arrayInputTest, $this->output);

        $namespacePascal = "Database\\Class\\{$connectionPascal}\\{$driver}\\{$entityPascal}";

        $fileC = "{$entityPascal}Controller";

        $pathC = "app/Http/Controllers/{$connectionPascal}/{$driver}/{$fileC}.php";

        $variableName = lcfirst($entityPascal);

        $this->fileWriter->readFileRows($pathC, [
            7 => [
                'replace' => true,
                'content' => ";\nuse {$namespacePascal};",
                'search' => ';',
            ],
            20 => [
                'replace' => true,
                'content' => <<<EOT
                *
                     * @param {$entityPascal} \${$variableName} [Parameter Description]
                EOT,
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
                'content' => <<<EOT
                *
                     * @param {$entityPascal} \${$variableName} [Parameter Description]
                EOT,

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
                'content' => <<<EOT
                *
                     * @param {$entityPascal} \${$variableName} [Parameter Description]
                EOT,
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

            foreach ($gettersCallModel as $keyGetterCallModel => $methods) {
                if (count($methods) > 1) {
                    $sizeMethods = count($methods) - 1;

                    foreach ($methods as $key => $name) {
                        if ($sizeMethods === $key) {
                            $listGettersCallModel[$keyGetterCallModel] .= <<<EOT
                                        \${$variableName}->{$name}(),
                            EOT;
                        } else {
                            $listGettersCallModel[$keyGetterCallModel] .= <<<EOT
                                    \${$variableName}->{$name}(),

                            EOT;
                        }
                    }
                } else {
                    $listGettersCallModel[$keyGetterCallModel] .= <<<EOT
                            \${$variableName}->{$methods[0]}(),
                    EOT;
                }
            }

            $this->fileWriter->readFileRows($pathM, [
                6 => [
                    'replace' => false,
                    'content' => "\nuse {$namespacePascal};\n",
                ],
                20 => [
                    'replace' => true,
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                    'search' => '*',
                ],
                23 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                            'content' => <<<EOT
                            [
                                {$listGettersCallModel['create']}
                                    ]
                            EOT,
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
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                    'search' => '*',
                ],
                46 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                            'content' => <<<EOT
                            [
                                {$listGettersCallModel['update']}
                                    ]
                            EOT,
                            'search' => '[]',
                        ],
                    ],
                ],
                54 => [
                    'replace' => true,
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                    'search' => '*',
                ],
                57 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                            'content' => <<<EOT
                            [
                                {$listGettersCallModel['delete']}
                                    ]
                            EOT,
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
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                ],
                23 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                    'search' => '*',
                ],
                46 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                            'content' => '',
                            'search' => ', []',
                        ],
                    ],
                ],
                54 => [
                    'replace' => true,
                    'content' => <<<EOT
                    *
                         * @param {$entityPascal} \${$variableName} [Parameter Description]
                         *
                    EOT,
                    'search' => '*',
                ],
                57 => [
                    'replace' => true,
                    'content' => <<<EOT
                    ({$entityPascal} \${$variableName})
                    EOT,
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
                            'content' => '',
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
     *
     * @throws ExceptionInterface
     *
     * @codeCoverageIgnore
     */
    private function addCapsule(
        string $driver,
        string $entity,
        string $connectionPascal,
        string $entityPascal,
        OutputInterface $output
    ): void {
        $inputArrayCapsule = new ArrayInput([
            'entity' => $entity,
        ]);

        $inputArrayTest = new ArrayInput([
            'test' => "Database/Class/{$connectionPascal}/{$driver}/{$entityPascal}Test",
        ]);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('db:capsule')
            ->run($inputArrayCapsule, $output);

        /** @phpstan-ignore-next-line */
        $this
            ->getApplication()
            ->find('new:test')
            ->run($inputArrayTest, $output);
    }

    /**
     * Generates the calls of the getter methods in the models, to add this
     * content to the generated model
     *
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param array<int, stdClass> $columns [List of defined columns]
     *
     * @return array{
     *     create: array<int, string>,
     *     update: array<int, string>,
     *     delete: array<int, string>
     * }
     *
     * @codeCoverageIgnore
     */
    private function generateCallGettersModel(string $entityPascal, array $columns): array
    {
        $methods = [
            'create' => [],
            'update' => [],
            'delete' => [],
        ];

        foreach (self::METHODS as $method) {
            foreach ($columns as $column) {
                /** @var string $field */
                $field = $column->Field;

                /** @var string $type */
                $type = $column->Type;

                $getter = $this->classFactory->getProperty(
                    $field,
                    $entityPascal,
                    $this->classFactory->getDBType($type)
                );

                /** @var stdClass $getterInfo */
                $getterInfo = $getter->getter;

                /** @var string $getterName */
                $getterName = $getterInfo->name;

                if ('create' === $method  && 'PRI' != $column->Key) {
                    $methods[$method][] = $getterName;
                }

                if ('update' === $method) {
                    $methods[$method][] = $getterName;
                }

                if ('delete' === $method && 'PRI' === $column->Key) {
                    $methods[$method][] = $getterName;
                }
            }
        }

        foreach ($columns as $column) {
            if ('PRI' === $column->Key) {
                /** @var array<int, string> $methodList */
                $methodList = $this->arr
                    ->of($methods['update'])
                    ->where(fn ($value, $key) => $key != 0)
                    ->get();

                /** @var string $firstMethod */
                $firstMethod = reset($methods['update']);

                $methods['update'] = [
                    ...$methodList,
                    $firstMethod
                ];
            }
        }

        return $methods;
    }
}
