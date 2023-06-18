<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RulesDBCommand extends Command {

    use ClassPath;

	protected static $defaultName = "db:rules";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command to generate the rules of an entity")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $entity = $input->getArgument("entity");
        $connection = $input->getOption("connection");

        $entity_pascal = str->of($entity)->replace("_", " ")->pascal()->get();
        $connections = DB::getConnections();
        $main_conn = ($connection === null) ? $connections['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->replace("-", " ")->pascal()->get();

        $columns = DB::connection($main_conn)->show()->full()->columns()->from($entity)->getAll();

        foreach ($columns as $key => $column) {
            // generate rule name
            $rule_name = str->of($column->Field)->replace("-", "_")->replace("_", " ")->trim()->pascal()->concat("Rule")->get();

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
                11 => [
                    'replace' => true,
                    'content' => '"' . $column->Field . '"', 'search' => '""'
                ],
                12 => [
                    'replace' => true,
                    'content' => '"' . $column->Comment . '"',
                    'search' => '""'
                ],
                14 => [
                    'replace' => true,
                    'content' => ($column->Null === "NO" ? "false" : "true"),
                    'search' => 'false'
                ],
                18 => [
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

        return Command::SUCCESS;
    }

}
