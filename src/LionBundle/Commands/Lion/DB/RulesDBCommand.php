<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use stdClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates the base rules for the properties of an entity
 *
 * @property FileWriter $fileWrite [FileWriter class object]
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
     * @required
     * */
    public function setFileWriter(FileWriter $fileWriter): RulesDBCommand
    {
        $this->fileWriter = $fileWriter;

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

        $entityPascal = $this->str->of($entity)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $connectionPascal = $this->str->of($selectedConnection)->replace('_', ' ')->replace('-', ' ')->pascal()->get();

        $columns = DB::connection($selectedConnection)->show()->full()->columns()->from($entity)->getAll();

        if (isset($columns->status)) {
            $output->writeln($this->errorOutput($columns->message));

            return Command::FAILURE;
        }

        $foreigns = DB::connection($selectedConnection)
            ->table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE', false)
            ->select('COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME')
            ->where()->equalTo('TABLE_SCHEMA', $selectedConnection)
            ->and()->equalTo('TABLE_NAME', $entity)
            ->and('REFERENCED_TABLE_NAME')->isNotNull()
            ->getAll();

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
                        $connectionPascal,
                        $entityPascal,
                        $column,
                        'Optional',
                        $output
                    );

                    $this->generateRule(
                        $connectionPascal,
                        $entityPascal,
                        $column,
                        'Required',
                        $output
                    );
                } else {
                    $this->generateRule(
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
            ->run(new ArrayInput(['rule' => "{$connectionPascal}/MySQL/{$entityPascal}/{$ruleName}"]), $output);

        $this->fileWriter->readFileRows("app/Rules/{$connectionPascal}/MySQL/{$entityPascal}/{$ruleName}.php", [
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
                'content' => "'{$column->Default}'",
                'search' => "''"
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
