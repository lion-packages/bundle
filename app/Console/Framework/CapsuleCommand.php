<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use LionSQL\Drivers\MySQLDriver as Builder;
use App\Traits\Framework\ClassPath;

class CapsuleCommand extends Command {

	protected static $defaultName = "database:capsule";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        Builder::init([
            'host' => env->DB_HOST,
            'port' => env->DB_PORT,
            'db_name' => env->DB_NAME,
            'user' => env->DB_USER,
            'password' => env->DB_PASSWORD
        ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required for the creation of new Capsules'
        )->addArgument(
            'capsule', InputArgument::REQUIRED, '', null
        )->addOption(
            'path', null, InputOption::VALUE_REQUIRED, 'Do you want to configure your own route?'
        )->addOption(
            'message', null, InputOption::VALUE_REQUIRED, ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = $input->getArgument('capsule');
        $path = $input->getOption('path');
        $message = $input->getOption('message');

        if ($message === null) {
            $message = true;
        }

        if ($message) {
            $output->writeln("<comment>Creating capsule...</comment>");
        }

        if ($path === null) {
            $path = "";
        }

        $normalize = function($value) {
            $str = trim(str_replace("_", " ", $value));
            $str = trim(ucwords($str));
            return trim(str_replace(" ", "", $str));
        };

        $list = ClassPath::export("Database/Class/", ($path . $normalize($table)));
        $columns = Builder::showColumns($table);
        $count = count($columns);
        $functions_union = "";
        // $parameters_union = "";
        // $variables_union = "";

        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Manage::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\r\n\n");
        ClassPath::add("namespace {$list['namespace']};\r\n\n");
        ClassPath::add("class {$list['class']} implements \JsonSerializable {\r\n\n");

        $addType = function($type) {
            if (preg_match("/^int|bigint/", $type)) {
                return "int";
            } elseif (preg_match("/^float/", $type)) {
                return "float";
            } else {
                return "string";
            }
        };

        $cleanField = function($field) {
            $field = str_replace(" ", "_", $field);
            return str_replace("-", "_", $field);
        };

        // Propierties
        foreach ($columns as $key => $column) {
            $field = $cleanField($column->Field);
            $object = '$' . strtolower($list['class']);
            $request_field = "request->{$field}";

            if ($key === 0) {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n");
                // $parameters_union.= "\t\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";

                if ($key === ($count - 1)) {
                    ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                    $functions_union.= $object . ' = new ' . "{$list['class']}();\n\n\t\t";
                    $functions_union.= $object . '->set' . $normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n\t\t" . "return {$object};";
                } else {
                    ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                    $functions_union.= $object . ' = new ' . "{$list['class']}();\n\n\t\t";
                    $functions_union.= $object . '->set' . $normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n";
                }
            } elseif ($key === ($count - 1)) {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n\n");
                // $parameters_union.= "\t\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null' . "\n\t";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";";
                ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                $functions_union.= "\t\t" . $object . '->set' . $normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n\t\t" . "return {$object};";
            } else {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n");
                // $parameters_union.= "\t\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";
                ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                $functions_union.= "\t\t" . $object . '->set' . $normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n";
            }
        }

        // Constructor
        // ClassPath::add("\tpublic function __construct({$parameters_union}) {}\n\n");
        ClassPath::add("\n\tpublic function __construct() {}\n\n");
        ClassPath::add("\tpublic function jsonSerialize(): mixed {\n\t\t" . 'return get_object_vars($this);' . "\n\t}\n\n");
        ClassPath::add("\tpublic static function formFields(): {$list['class']} {\n\t\t{$functions_union}\n\t}\n\n");

        // Getters and Setters
        foreach ($columns as $key => $column) {
            $field = $cleanField($column->Field);

            ClassPath::add("\tpublic function get" . $normalize($field) . "(): ?" . $addType($column->Type) . " {\n\t\t");
            ClassPath::add('return $this->' . $field . ";");
            ClassPath::add("\n\t}\n\n");

            ClassPath::add("\tpublic function set" . $normalize($field) . '(?' . $addType($column->Type) . ' $' . $field . "): {$list['class']} {\n\t\t");
            ClassPath::add('$this->' . $field . " =" . ' $' . "{$field};\n\t\t");
            ClassPath::add('return $this;');
            ClassPath::add("\n\t}\n\n");
        }

        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        if ($message) {
            $output->writeln("<info>Capsule created successfully</info>");
        }

        return Command::SUCCESS;
    }

}