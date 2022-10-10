<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionCommand\Functions\{ FILES, ClassPath };
use LionSQL\Drivers\MySQLDriver as Builder;

class CapsuleCommand extends Command {

	protected static $defaultName = "new:capsule";
    private string $default_path = "app/Class/";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating capsule...</comment>");

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
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = $input->getArgument('capsule');
        $path = $input->getOption('path');

        if ($path === null) {
            $path = "";
        }

        $normalize = function($value) {
            $str = trim(str_replace("_", " ", $value));
            $str = trim(ucwords($str));
            return trim(str_replace(" ", "", $str));
        };

        $list = ClassPath::export($this->default_path, ($path . $normalize($table)));
        $columns = Builder::showColumns($table);
        $count = count($columns);
        $parameters_union = "";
        // $variables_union = "";

        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        FILES::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\r\n\n");
        ClassPath::add("namespace {$list['namespace']};\r\n\n");
        ClassPath::add("class {$list['class']} {\r\n\n");

        $addType = function($type) {
            return preg_match("/^int|bigint/", $type) ? "int" : "string";
        };

        // Propierties
        foreach ($columns as $key => $column) {
            if ($key === 0 ) {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n");
                $parameters_union.= "\n\t\tprivate ?" . $addType($column->Type) . ' $' . $column->Field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";
            } elseif ($key === ($count - 1)) {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n\n");
                $parameters_union.= "\t\tprivate ?" . $addType($column->Type) . ' $' . $column->Field . ' = null' . "\n\t";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";";
            } else {
                // ClassPath::add("\tprivate ?" . $addType($column->Type) . ' $' . "{$column->Field};\n");
                $parameters_union.= "\t\tprivate ?" . $addType($column->Type) . ' $' . $column->Field . ' = null,' . "\n";
                // $variables_union.= "\t\t" . '$this->' . $column->Field . ' = $' . $column->Field . ";\n";
            }
        }

        // Constructor
        ClassPath::add("\tpublic function __construct({$parameters_union}) {}\n\n");

        // Getters and Setters
        foreach ($columns as $key => $column) {
            ClassPath::add("\tpublic function get" . $normalize($column->Field) . "(): " . $addType($column->Type) . " {\n\t\t");
            ClassPath::add('return $this->' . $column->Field . ";");
            ClassPath::add("\n\t}\n\n");

            ClassPath::add("\tpublic function set" . $normalize($column->Field) . '(?' . $addType($column->Type) . ' $' . $column->Field . "): {$list['class']} {\n\t\t");
            ClassPath::add('$this->' . $column->Field . " =" . ' $' . "{$column->Field};\n\t\t");
            ClassPath::add('return $this;');
            ClassPath::add("\n\t}\n\n");
        }

        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Capsule created successfully</info>");
        return Command::SUCCESS;
    }

}