<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a CRUD (Controllers, Models, Tests and Capsule Classes) of an entity
 *
 * @property FileWriter $fileWriter [Object of class FileWriter]
 * @property ClassFactory $classFactory [Object of class ClassFactory]
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
    const METHODS = ['create', 'update', 'delete'];

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
     * @required
     * */
    public function setFileWriter(FileWriter $fileWriter): CrudCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): CrudCommand
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');

        $selectedConnection = $this->selectConnection($input, $output);

        $connectionPascal = $this->str->of($selectedConnection)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $entityPascal = $this->str->of($entity)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $columns = DB::connection($selectedConnection)->show()->columns()->from($entity)->getAll();

        if (isError($columns)) {
            $output->writeln($this->errorOutput("\t>>  CRUD: {$columns->message}"));

            return Command::FAILURE;
        }

        $this->addDBRules($entity, $output);

        $this->addControllerAndModel(
            $entityPascal,
            $connectionPascal,
            "Database\\Class\\{$connectionPascal}\\MySQL\\{$entityPascal}",
            $entity,
            $columns,
            $output
        );

        $this->addCapsule($entity, $selectedConnection, $entityPascal, $output);

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
            ->run(new ArrayInput(['entity' => $entity]), $output);
    }

    /**
     * Create the controller and model of an entity
     *
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param string $connectionPascal [Connection name in pascal-case format]
     * @param string $namespacePascal [Namespace name in pascal-case format]
     * @param string $entity [Entity name]
     * @param array $columns [List of defined columns]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function addControllerAndModel(
        string $entityPascal,
        string $connectionPascal,
        string $namespacePascal,
        string $entity,
        array $columns,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('new:controller')
            ->run(
                new ArrayInput([
                    'controller' => "{$connectionPascal}/MySQL/{$entityPascal}Controller",
                    '--model' => "{$connectionPascal}/MySQL/{$entityPascal}Model"
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput([
                    'test' => "App/Http/Controllers/{$connectionPascal}/MySQL/{$entityPascal}ControllerTest"
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput(['test' => "App/Models/{$connectionPascal}/MySQL/{$entityPascal}ModelTest"]),
                $output
            );

        $fileC = "{$entityPascal}Controller";

        $pathC = "app/Http/Controllers/{$connectionPascal}/MySQL/{$fileC}.php";

        $this->fileWriter->readFileRows($pathC, [
            8 => [
                'replace' => false,
                'content' => "use {$namespacePascal};\nuse stdClass;\n"
            ],
            19 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            24 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            26 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . '->capsule())',
                'search' => '()'
            ],
            43 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            49 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            51 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . '->capsule())',
                'search' => '()'
            ],
            56 => [
                'replace' => true,
                'content' => "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . ' [Parameter Description]',
                'search' => '*'
            ],
            62 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            64 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . ')',
                'search' => '()'
            ]
        ]);

        $pathM = "app/Models/{$connectionPascal}/MySQL/{$entityPascal}Model.php";

        $gettersCallModel = $this->generateCallGettersModel($entityPascal, $columns);

        $listGettersCallModel = ['create' => '', 'update' => '', 'delete' => ''];

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
                'content' => "\nuse {$namespacePascal};\n"
            ],
            19 => [
                'replace' => true,
                'content' => (
                    "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                    "\t *"
                ),
                'search' => '*'
            ],
            22 => [
                'replace' => true,
                'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            24 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => "'create_{$entity}'",
                        'search' => "''"
                    ],
                    [
                        'content' => "[\n{$listGettersCallModel['create']}\t\t]",
                        'search' => '[]'
                    ]
                ]
            ],
            35 => [
                'replace' => true,
                'content' => "'read_{$entity}'",
                'search' => "''"
            ],
            42 => [
                'replace' => true,
                'content' => (
                    "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                    "\t *"
                ),
                'search' => '*'
            ],
            45 => [
                'replace' => true,
                'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            47 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => "'update_{$entity}'",
                        'search' => "''"
                    ],
                    [
                        'content' => "[\n{$listGettersCallModel['update']}\t\t]",
                        'search' => '[]'
                    ]
                ]
            ],
            53 => [
                'replace' => true,
                'content' => (
                    "*\n\t * @param {$entityPascal} " . '$' . lcfirst($entityPascal) . " [Parameter Description]\n" .
                    "\t *"
                ),
                'search' => '*'
            ],
            56 => [
                'replace' => true,
                'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            58 => [
                'replace' => true,
                'multiple' => [
                    [
                        'content' => "'delete_{$entity}'",
                        'search' => "''"
                    ],
                    [
                        'content' => "[\n{$listGettersCallModel['delete']}\t\t]",
                        'search' => '[]'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Create an entity capsule
     *
     * @param string $entity [Entity name]
     * @param string $selectedConnection [Selected connection]
     * @param string $entityPascal [Entity name in pascal-case format]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function addCapsule(
        string $entity,
        string $selectedConnection,
        string $entityPascal,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('db:capsule')
            ->run(new ArrayInput(['entity' => $entity]), $output);

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput(['test' => "Database/Class/{$selectedConnection}/MySQL/{$entityPascal}Test",]),
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

        foreach (['create', 'update', 'delete'] as $method) {
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
