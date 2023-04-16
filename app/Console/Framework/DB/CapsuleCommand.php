<?php

namespace App\Console\Framework\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use LionSQL\Drivers\MySQL as DB;
use App\Traits\Framework\ClassPath;
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
            'connection', null, InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?'
        )->addOption(
            'message', null, InputOption::VALUE_REQUIRED, ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = $input->getArgument('capsule');
        $path = $input->getOption('path');
        $connection = $input->getOption('connection');
        $message = $input->getOption('message');
        $columns = null;

        if ($message === null) {
            $message = true;
        }

        if ($message) {
            $output->writeln("<comment>Creating capsule...</comment>");
        }

        if ($path === null) {
            $path = "";
        }

        if ($connection === null) {
            $columns = DB::table($table)->show()->columns()->getAll();
        } else {
            $columns = DB::connection($connection)->table($table)->show()->columns()->getAll();
        }

        $index = 0;
        $list = ClassPath::export("Database/Class/", ($path . ClassPath::normalizeClass($table)));
        $functions_union = "";
        $propierties_union = "";
        $new_object_union = "";
        $object_class = "";

        $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
        Store::folder($url_folder);
        ClassPath::create($url_folder, $list['class']);
        ClassPath::add(Str::of("<?php")->ln()->ln()->get());
        ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";\r\n\n")->get());
        ClassPath::add(Str::of("class ")->concat($list['class'])->concat(" implements \JsonSerializable {\n\n")->get());

        // Propierties
        foreach ($columns as $key => $column) {
            if ($index === 0) {
                $new_object_union = ClassPath::addNewObjectClass($list['class']);
                $object_class = Str::of('$')->concat($list['class'])->lower();
                $index++;
            }

            $field = ClassPath::cleanField($column->Field);
            $normalize_field = ClassPath::normalizeField($field, true);
            $prop = Str::of('request->')->concat($normalize_field)->get();
            $propierties_union .= ClassPath::addPropierty($column->Type, $field);
            $functions_union .= Str::of(
                ClassPath::addSetFunctionIsset($object_class, $field, $prop)
            )->ln()->get();
        }

        // Class
        ClassPath::add($propierties_union);
        ClassPath::add(Str::of("\n\tpublic function __construct() {\n\n")->concat("\t}\n\n")->get());
        ClassPath::add("\tpublic function jsonSerialize(): mixed {\n\t\t" . 'return get_object_vars($this);' . "\n\t}\n\n");

        ClassPath::add(
            Str::of("\tpublic static function formFields(): ")
            ->concat($list['class'])
            ->concat(" {\n\t\t")
            ->concat($new_object_union)
            ->concat($functions_union)
            ->concat("\t\treturn ")
            ->concat($object_class)
            ->concat(";")
            ->concat("\n\t}\n\n")
            ->get()
        );

        // Getters and Setters
        foreach ($columns as $key => $column) {
            $field = ClassPath::cleanField($column->Field);
            $normalize_field = ClassPath::normalizeField($field, true);

            ClassPath::add("\tpublic function get" . ClassPath::normalizeClass($field) . "(): ?" . ClassPath::addType($column->Type) . " {\n\t\t");
            ClassPath::add('return $this->' . $normalize_field . ";");
            ClassPath::add("\n\t}\n\n");

            ClassPath::add(
                Str::of(ClassPath::addSetFunction($column->Type, $normalize_field, $list['class']))
                    ->concat(" {\n")
                    ->concat("\t\t")
                    ->concat('$this->')
                    ->concat($normalize_field)
                    ->concat(" = $")
                    ->concat($normalize_field)
                    ->concat(";\n\t\t")
                    ->concat('return $this;')
                    ->concat("\n\t}\n\n")
                    ->get()
            );
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