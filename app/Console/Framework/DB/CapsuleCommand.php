<?php

namespace App\Console\Framework\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Manage;
use LionSQL\Drivers\MySQLDriver as DB;
use App\Traits\Framework\ClassPath;
use LionHelpers\Arr;
use LionHelpers\Str;

class CapsuleCommand extends Command {

	protected static $defaultName = "db:capsule";

    protected function initialize(InputInterface $input, OutputInterface $output) {

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

        $list = ClassPath::export("Database/Class/", ($path . ClassPath::normalize($table)));
        $columns = DB::table($table)->show()->columns()->getAll();
        $count = Arr::of($columns)->length();
        $functions_union = "";
        // $parameters_union = "";
        // $variables_union = "";

        $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
        Manage::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add(Str::of("<?php\r")->ln()->ln()->get());
        ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";\r\n\n")->get());
        ClassPath::add(Str::of("class ")->concat($list['class'])->concat(" implements \JsonSerializable {\r")->ln()->ln()->get());

        // Propierties
        foreach ($columns as $key => $column) {
            $field = ClassPath::cleanField($column->Field);
            $object = Str::of('$')->concat($list['class'])->lower();
            $request_field = Str::of('request->')->concat($field)->get();

            if ($key === 0) {
                // ClassPath::add("\tprivate ?" . ClassPath::addType($column->Type) . ' $' . "{$column->Field};\n");
                // $parameters_union.= "\t\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";

                if ($key === ($count - 1)) {
                    ClassPath::add(
                        Str::of("\tprivate ?")->concat(ClassPath::addType($column->Type))->concat(' ?')->concat($field)->concat(' = null;')->ln()->get()
                    );

                    $functions_union.= Str::of($object)->concat(' = new ')->concat($list['class'])->concat("();")->ln()->ln()->concat("\t\t")->get();

                    $functions_union.= Str::of($object)->concat('->set')->concat(ClassPath::normalize($field))->concat("(\n\t\t\tisset(")->concat($request_field)->concat(") ? ")->concat($request_field)->concat(" : null\n\t\t);\n\n\t\treturn ")->concat($object)->concat(";")->get();
                } else {
                    ClassPath::add(
                        "\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null;' . "\n"
                    );
                    $functions_union.= $object . ' = new ' . "{$list['class']}();\n\n\t\t";
                    $functions_union.= $object . '->set' . ClassPath::normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n";
                }
            } elseif ($key === ($count - 1)) {
                // ClassPath::add("\tprivate ?" . ClassPath::addType($column->Type) . ' $' . "{$column->Field};\n\n");
                // $parameters_union.= "\t\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null' . "\n\t";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";";
                ClassPath::add("\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                $functions_union.= "\t\t" . $object . '->set' . ClassPath::normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n\t\t" . "return {$object};";
            } else {
                // ClassPath::add("\tprivate ?" . ClassPath::addType($column->Type) . ' $' . "{$column->Field};\n");
                // $parameters_union.= "\t\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";
                ClassPath::add("\tprivate ?" . ClassPath::addType($column->Type) . ' $' . $field . ' = null;' . "\n");
                $functions_union.= "\t\t" . $object . '->set' . ClassPath::normalize($field) . "(\n\t\t\t" . "isset({$request_field}) ? {$request_field} : null" . "\n\t\t);\n\n";
            }
        }

        // Constructor
        // ClassPath::add("\tpublic function __construct({$parameters_union}) {}\n\n");
        ClassPath::add("\n\tpublic function __construct() {}\n\n");
        ClassPath::add("\tpublic function jsonSerialize(): mixed {\n\t\t" . 'return get_object_vars($this);' . "\n\t}\n\n");
        ClassPath::add("\tpublic static function formFields(): {$list['class']} {\n\t\t{$functions_union}\n\t}\n\n");

        // Getters and Setters
        foreach ($columns as $key => $column) {
            $field = ClassPath::cleanField($column->Field);

            ClassPath::add("\tpublic function get" . ClassPath::normalize($field) . "(): ?" . ClassPath::addType($column->Type) . " {\n\t\t");
            ClassPath::add('return $this->' . $field . ";");
            ClassPath::add("\n\t}\n\n");

            ClassPath::add("\tpublic function set" . ClassPath::normalize($field) . '(?' . ClassPath::addType($column->Type) . ' $' . $field . "): {$list['class']} {\n\t\t");
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