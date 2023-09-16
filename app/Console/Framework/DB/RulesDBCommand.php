<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RulesDBCommand extends Command
{
    use ClassPath;

	protected static $defaultName = "db:rules";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Command to generate the rules of an entity")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument("entity");
        $connection = $input->getOption("connection");

        $entity_pascal = str->of($entity)->replace("_", " ")->pascal()->get();
        $connections = DB::getConnections();
        $main_conn = ($connection === null) ? $connections['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->replace("-", " ")->pascal()->get();

        $columns = DB::connection($main_conn)
            ->show()
            ->full()
            ->columns()
            ->from($entity)
            ->getAll();

        $foreigns = DB::connection($main_conn)
            ->table("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", true)
            ->select("COLUMN_NAME", "REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME")
            ->where(DB::equalTo("TABLE_SCHEMA"), $main_conn)
            ->and(DB::equalTo("TABLE_NAME"), $entity)
            ->and("REFERENCED_TABLE_NAME")
            ->isNotNull()
            ->getAll();

        foreach ($columns as $keyColumn => $column) {
            $is_foreign = false;

            if (!isset($foreigns->status)) {
                if (is_array($foreigns)) {
                    foreach ($foreigns as $keyForeign => $foreign) {
                        if ($column->Field === $foreign->COLUMN_NAME) {
                            $is_foreign = true;
                            break;
                        }
                    }
                } else {
                    if ($column->Field === $foreigns->COLUMN_NAME) {
                        $is_foreign = true;
                    }
                }
            }

            if (!$is_foreign) {
                // generate rule name
                $rule_name = str->of($column->Field)
                    ->replace("-", "_")
                    ->replace("_", " ")
                    ->trim()
                    ->pascal()
                    ->concat("Rule")
                    ->get();

                // generate rule
                $this->getApplication()->find('new:rule')->run(
                    new ArrayInput([
                        'rule' => "{$main_conn_pascal}/{$entity_pascal}/{$rule_name}"
                    ]),
                    $output
                );

                // edit rule content
                $path = "app/Rules/{$main_conn_pascal}/{$entity_pascal}/{$rule_name}.php";
                $this->readFileRows($path, [
                    14 => [
                        'replace' => true,
                        'content' => '"' . $column->Field . '"',
                        'search' => '""'
                    ],
                    15 => [
                        'replace' => true,
                        'content' => '"' . $column->Comment . '"',
                        'search' => '""'
                    ],
                    16 => [
                        'replace' => true,
                        'content' => ($column->Null === "NO" ? "false" : "true"),
                        'search' => 'false'
                    ],
                    22 => [
                        'replace' => true,
                        'multiple' => [
                            [
                                'content' => ($column->Null === "NO" ? "required" : "optional"),
                                'search' => 'required'
                            ]
                        ]
                    ]
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
