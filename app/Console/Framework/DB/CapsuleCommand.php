<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionHelpers\Str;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class CapsuleCommand extends Command {

    use ClassPath;

	protected static $defaultName = "db:capsule";

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription('Command required for the creation of new Capsules')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name', null)
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?')
            ->addOption('message', null, InputOption::VALUE_REQUIRED, '', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = $input->getArgument('entity');
        $connection = $input->getOption('connection');
        $message = $input->getOption('message');

        $columns = null;
        $table = Str::of($table)->test("/-/") ? "`{$table}`" : $table;
        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->replace("-", " ")->pascal()->get();

        if ($message === null) {
            $message = true;
            $output->writeln("<comment>\t>>  CAPSULE: {$table}</comment>");
        }

        $columns = DB::connection($main_conn)->show()->columns()->from($table)->getAll();
        if (isset($columns->status)) {
            $output->writeln("<fg=#E37820>\t>>  CAPSULE: {$columns->message} \u{2717}</>");
            return Command::FAILURE;
        }

        $index = 0;
        $list = $this->export("Database/Class/{$main_conn_pascal}/", $this->normalizeClass(Str::of($table)->replace('`', '')->get()));
        $functions_union = "";
        $propierties_union = "";
        $new_object_union = "";
        $object_class = "";

        $url_folder = lcfirst(Str::of($list['namespace'])->replace("\\", "/")->get());
        Store::folder($url_folder);
        $this->create($url_folder, $list['class']);
        $this->add(Str::of("<?php")->ln()->ln()->get());
        $this->add(Str::of("namespace ")->concat($list['namespace'])->concat(";\r\n\n")->get());
        $this->add(
            Str::of("class ")
                ->concat($list['class'])
                ->concat(" implements \JsonSerializable {")->ln()->ln()
                ->get()
        );

        // Propierties
        foreach ($columns as $key => $column) {
            if ($index === 0) {
                $new_object_union = $this->addNewObjectClass($list['class']);
                $object_class = Str::of('$')->concat(lcfirst($list['class']))->trim()->get();
                $index++;
            }

            $field = $this->cleanField($column->Field);
            $normalize_field = $this->normalizeField($field, true);
            $prop = Str::of('request->')->concat($normalize_field)->get();
            $propierties_union .= $this->addPropierty($column->Type, $field);
            $functions_union .= Str::of($this->addSetFunctionIsset($object_class, $field, $prop))->ln()->get();
        }

        // Class
        $this->add($propierties_union);
        $this->add(Str::of("\n\tpublic function __construct() {\n\n")->concat("\t}\n\n")->get());
        $this->add("\tpublic function jsonSerialize(): mixed {\n\t\t" . 'return get_object_vars($this);' . "\n\t}\n\n");

        $this->add(
            Str::of("\tpublic static function capsule(): ")
                ->concat($list['class'])
                ->concat(" {")->ln()->lt()->lt()
                ->concat($new_object_union)
                ->concat($functions_union)->lt()->lt()
                ->concat("return ")
                ->concat($object_class)
                ->concat(";")->ln()->lt()
                ->concat("}")->ln()->ln()
                ->get()
        );

        // Getters and Setters
        foreach ($columns as $key => $column) {
            $field = $this->cleanField($column->Field);
            $normalize_field = $this->normalizeField($field, true);

            $this->add("\tpublic function get" . $this->normalizeClass($field) . "(): ?" . $this->addType($column->Type) . " {\n\t\t");
            $this->add('return $this->' . $normalize_field . ";");
            $this->add("\n\t}\n\n");

            $this->add(
                Str::of($this->addSetFunction($column->Type, $normalize_field, $list['class']))
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

        $this->add("}");
        $this->force();
        $this->close();

        if ($message != null) {
            $output->writeln("<info>\t>>  CAPSULE: The '{$list['namespace']}\\{$list['class']}' capsule has been generated</info>");
        }

        return Command::SUCCESS;
    }

}
