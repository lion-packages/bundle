<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrudCommand extends Command {

	protected static $defaultName = "db:crud";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->write("\033[2J\033[;H");
        $output->writeln("<comment>Generating CRUD...</comment>\n");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("command to generate controller and model of an entity with their respective CRUD functions")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $entity = $input->getArgument("entity");
        $connection = $input->getOption("connection");
        $path = $input->getOption("path");

        $entity_pascal = str->of($entity)->replace("_", " ")->pascal()->get();
        $default_connection = DB::getConnections();
        $main_conn = $connection === null ? $default_connection['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->pascal()->get();
        $namespace_class = "Database\\Class\\{$main_conn_pascal}\\{$entity_pascal}";
        $list = ClassPath::export("database/class/", "{$main_conn_pascal}/{$entity_pascal}");

        $this->getApplication()->find('db:capsule')->run(
            new ArrayInput([
                'capsule' => $entity,
                '--path' => $main_conn_pascal . "/",
                '--connection' => $main_conn
            ]),
            $output
        );

        $this->getApplication()->find('new:controller')->run(
            new ArrayInput([
                'controller' => ($path === null ? "" : $path) . "{$entity_pascal}Controller",
                '--model' => ($path === null ? "" : $path) . "{$entity_pascal}Model"
            ]),
            $output
        );

        // modify controllers
        $file_c = "{$entity_pascal}Controller";
        $path_c = "app/Http/Controllers/" . ($path === null ? "" : $path) . "{$file_c}.php";

        ClassPath::readFileRows($path_c, [
            6 => ['replace' => false, 'content' => "use {$namespace_class};\n\n"],
            16 => ['replace' => true, 'content' => "(\n\t\t\t{$list['class']}::capsule()\n\t\t)", 'search' => "()"],
            29 => ['replace' => true, 'content' => "(\n\t\t\t{$list['class']}::capsule()\n\t\t)", 'search' => "()"],
            38 => ['replace' => true, 'content' => "(\n\t\t\t{$list['class']}::capsule()\n\t\t)", 'search' => "()"],
        ]);

        // modify models
        $path_m = "app/Models/" . ($path === null ? "" : $path) . "{$entity_pascal}Model.php";
        $list_methods = ['create' => "", 'update' => "", 'delete' => ""];
        $methods = ClassPath::generateGetters(
            DB::connection($main_conn)->table($entity)->show()->columns()->getAll()
        );

        foreach ($methods as $key => $method) {
            foreach ($method as $keyMethod => $name) {
                $list_methods[$key] .= str->of("\t")->lt()->lt()
                    ->concat('$')
                    ->concat(lcfirst($entity_pascal))
                    ->concat("->")
                    ->concat($name)
                    ->concat(",")->ln()
                    ->get();
            }
        }

        // vd(count($methods['create']));
        // vd(count($methods['update']));
        // vd(count($methods['delete']));
        // die;

        ClassPath::readFileRows($path_m, [
            4 => [ // namespace
                'replace' => false,
                'content' => "\nuse {$namespace_class};\n"
            ],
            14 => [ // parameter create entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            15 => [ // values create entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'create_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['create']}\t\t]", 'search' => '[]']
                ]
            ],
            20 => [ // read entity
                'replace' => true,
                'multiple' => [
                    ['content' => "table", 'search' => "view"],
                    ['content' => "'{$entity}'", 'search' => "''"]
                ]
            ],
            23 => [ // parameter update entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            24 => [ // values update entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'update_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['update']}\t\t]", 'search' => '[]']
                ]
            ],
            28 => [ // parameter delete entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            29 => [ // values update entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'delete_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['delete']}\t\t]", 'search' => '[]']
                ]
            ],
        ]);

        return Command::SUCCESS;
    }

}
