<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RulesDBCommand extends MenuCommand
{
    private FileWriter $fileWriter;

    /**
     * @required
     * */
    public function setFileWriter(FileWriter $fileWriter): RulesDBCommand
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('db:rules')
            ->setDescription('Command to generate the rules of an entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name');
    }

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
                $ruleName = $this->str
                    ->of($column->Field)
                    ->replace('-', '_')
                    ->replace('_', ' ')
                    ->trim()
                    ->pascal()
                    ->concat('Rule')
                    ->get();

                $this
                    ->getApplication()
                    ->find('new:rule')
                    ->run(new ArrayInput(['rule' => "{$connectionPascal}/MySQL/{$entityPascal}/{$ruleName}"]), $output);

                $this->fileWriter->readFileRows(
                    "app/Rules/{$connectionPascal}/MySQL/{$entityPascal}/{$ruleName}.php",
                    [
                        12 => [
                            'replace' => true,
                            'content' => "'" . strtolower($column->Field) . "'",
                            'search' => "''"
                        ],
                        19 => [
                            'replace' => true,
                            'content' => "'" . strtolower($column->Field) . "'",
                            'search' => "''"
                        ],
                        23 => [
                            'replace' => true,
                            'content' => "'" . strtolower($column->Field) . "'",
                            'search' => "''"
                        ],
                        26 => [
                            'replace' => true,
                            'content' => "'" . strtolower($column->Field) . "'",
                            'search' => "''"
                        ],
                        30 => [
                            'replace' => true,
                            'content' => "'{$column->Comment}'",
                            'search' => "''"
                        ],
                        33 => [
                            'replace' => true,
                            'content' => "'" . strtolower($column->Field) . "'",
                            'search' => "''"
                        ],
                        37 => [
                            'replace' => true,
                            'content' => "'{$column->Default}'",
                            'search' => "''"
                        ],
                        44 => [
                            'replace' => true,
                            'content' => ($column->Null === 'NO' ? 'false' : 'true'),
                            'search' => 'false'
                        ],
                        52 => [
                            'replace' => true,
                            'multiple' => [
                                [
                                    'content' => ($column->Null === 'NO' ? 'required' : 'optional'),
                                    'search' => 'required'
                                ]
                            ]
                        ]
                    ]
                );
            } else {
                $output->writeln($this->infoOutput("\t>>  RULE: the rule for '{$column->Field}' property has been omitted, it is a foreign"));
            }
        }

        return Command::SUCCESS;
    }
}
