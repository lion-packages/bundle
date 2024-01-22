<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\DB;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\SelectedDatabaseConnection;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrudCommand extends SelectedDatabaseConnection
{
    const METHODS = ['create', 'update', 'delete'];

    private Str $str;
    private FileWriter $fileWriter;
    private ClassFactory $classFactory;

    /**
     * @required
     * */
    public function setStr(Str $str): CrudCommand
    {
        $this->str = $str;

        return $this;
    }

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

    protected function configure(): void
    {
        $this
            ->setName('db:crud')
            ->setDescription(
                'command to generate controller and model of an entity with their respective CRUD functions'
            )
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');
        $selectedConnection = $this->selectConnection($input, $output);

        $connectionPascal = $this->str->of($selectedConnection)->replace('_', ' ')->replace('-', ' ')->pascal()->get();
        $entityPascal = $this->str->of($entity)->replace('_', ' ')->replace('-', ' ')->pascal()->get();
        $namespacePascal = "Database\\Class\\{$connectionPascal}\\MySQL\\{$entityPascal}";

        $this->addDBRules($entity, $output);

        $this->addControllerAndModel(
            $selectedConnection,
            $entityPascal,
            $connectionPascal,
            $namespacePascal,
            $entity,
            $output
        );

        $this->addCapsule($entity, $selectedConnection, $entityPascal, $output);

        $output->writeln($this->infoOutput("\t>>  CRUD: crud has been generated for the '{$entity}' entity"));

        return Command::SUCCESS;
    }

    private function addDBRules(string $entity, OutputInterface $output): void
    {
        $this
            ->getApplication()
            ->find('db:rules')
            ->run(new ArrayInput(['entity' => $entity]), $output);
    }

    private function addControllerAndModel(
        string $selectedConnection,
        string $entityPascal,
        string $connectionPascal,
        string $namespacePascal,
        string $entity,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('new:controller')
            ->run(
                new ArrayInput([
                    'controller' => "{$selectedConnection}/MySQL/{$entityPascal}Controller",
                    '--model' => "{$selectedConnection}/MySQL/{$entityPascal}Model"
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(
                new ArrayInput([
                    'test' => "Controllers/{$selectedConnection}/MySQL/{$entityPascal}ControllerTest",
                ]),
                $output
            );

        $this
            ->getApplication()
            ->find('new:test')
            ->run(new ArrayInput(['test' => "Models/{$selectedConnection}/MySQL/{$entityPascal}ModelTest",]), $output);

        $fileC = "{$entityPascal}Controller";
        $pathC = "app/Http/Controllers/{$connectionPascal}/MySQL/{$fileC}.php";

        $this->fileWriter->readFileRows($pathC, [
            8 => [
                'replace' => false,
                'content' => "use {$namespacePascal};\n\n"
            ],
            11 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            13 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            21 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            23 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            26 => [
                'replace' => true,
                'content' => ("{$entityPascal}({$entityPascal} " . '$' . lcfirst($entityPascal) . ', '),
                'search' => "{$entityPascal}("
            ],
            28 => [
                'replace' => true,
                'content' => '($' . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
        ]);

        $columns = DB::connection($selectedConnection)->show()->columns()->from($entity)->getAll();
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
            11 => [
                'replace' => true,
                'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            13 => [
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
            19 => [
                'replace' => true,
                'content' => "'read_{$entity}'",
                'search' => "''"
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
                        'content' => "'update_{$entity}'",
                        'search' => "''"
                    ],
                    [
                        'content' => "[\n{$listGettersCallModel['update']}\t\t]",
                        'search' => '[]'
                    ]
                ]
            ],
            28 => [
                'replace' => true,
                'content' => "({$entityPascal} $" . lcfirst($entityPascal) . ')',
                'search' => '()'
            ],
            30 => [
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
            ],
        ]);
    }

    private function addCapsule(
        string $entity,
        string $selectedConnection,
        string $entityPascal,
        OutputInterface $output
    ): void {
        $this
            ->getApplication()
            ->find('db:mysql:capsule')
            ->run(new ArrayInput(['entity' => $entity]), $output);

        $this
            ->getApplication()
            ->find('new:test')
            ->run(new ArrayInput(['test' => "Class/{$selectedConnection}/MySQL/{$entityPascal}Test",]), $output);
    }

    private function generateCallGettersModel(string $entityPascal, array $columns): array
    {
        $methods = ['create' => [], 'update' => [], 'delete' => []];

        foreach (['create', 'update', 'delete'] as $method) {
            foreach ($columns as $column) {
                $getter = $this->classFactory->getPropierty(
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
                    ...$this->arr->of($methods['update'])->where(fn($value, $key) => $key != 0)->get(),
                    reset($methods['update'])
                ];
            }
        }

        return $methods;
    }
}
