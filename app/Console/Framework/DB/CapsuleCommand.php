<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class CapsuleCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "db:capsule";

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required for the creation of new Capsules')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name', null)
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?')
            ->addOption('message', null, InputOption::VALUE_REQUIRED, '', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $input->getArgument('entity');
        $connection = $input->getOption('connection');
        $message = $input->getOption('message');

        $columns = null;
        $table = str->of($table)->test("/-/") ? "`{$table}`" : $table;
        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->replace("-", " ")->pascal()->get();

        if ($message === null) {
            $message = true;
            $output->writeln($this->warningOutput("\t>>  CAPSULE: {$table}"));
        }

        $columns = DB::connection($main_conn)->show()->columns()->from($table)->getAll();
        if (isset($columns->status)) {
            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$columns->message} \u{2717}"));
            return Command::FAILURE;
        }

        $index = 0;

        $list = $this->export(
            "Database/Class/{$main_conn_pascal}/",
            $this->normalizeClass(str->of($table)->replace('`', '')->get())
        );

        $functions_union = "";
        $propierties_union = "";
        $new_object_union = "";
        $object_class = "";

        $url_folder = lcfirst(str->of($list['namespace'])->replace("\\", "/")->get());
        Store::folder($url_folder);
        $this->create($url_folder, $list['class']);
        $this->add(str->of("<?php")->ln()->ln()->concat('declare(strict_types=1);')->ln()->ln()->get());

        $this->add(
            str->of("namespace")->spaces(1)
                ->concat($list['namespace'])
                ->concat(";")->ln()->ln()
                ->get()
        );

        $this->add(
            str->of('use JsonSerializable;')->ln()->ln()
                ->concat("class")->spaces(1)
                ->concat($list['class'])->spaces(1)
                ->concat("implements JsonSerializable\n{")->ln()
                ->get()
        );

        // Propierties
        foreach ($columns as $key => $column) {
            if ($index === 0) {
                $new_object_union = $this->addNewObjectClass($list['class']);
                $object_class = str->of('$')->concat(lcfirst($list['class']))->trim()->get();
                $index++;
            }

            $field = $this->cleanField($column->Field);
            $normalize_field = $this->normalizeField($field, true);
            $prop = str->of('request->')->concat($normalize_field)->get();
            $propierties_union .= $this->addPropierty($column->Type, $field);
            $functions_union .= str->of($this->addSetFunctionIsset($object_class, $field, $prop))->ln()->get();
        }

        // Class
        $this->add($propierties_union);
        // $this->add(str->of("\n\tpublic function __construct()\n\t{\n\n")->concat("\t}\n\n")->get());
        $this->add("\n\tpublic function jsonSerialize(): mixed\n\t{\n\t\t" . 'return get_object_vars($this);' . "\n\t}\n\n");

        $this->add(
            str->of("\tpublic static function capsule(): ")
                ->concat($list['class'])
                ->concat("\n\t{")->ln()->lt()->lt()
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

            $this->add("\tpublic function get" . $this->normalizeClass($field) . "(): ?" . $this->addType($column->Type) . "\n\t{\n\t\t");
            $this->add('return $this->' . $normalize_field . ";");
            $this->add("\n\t}\n\n");

            $this->add(
                str->of($this->addSetFunction($column->Type, $normalize_field, $list['class']))
                    ->concat("\n\t{\n")
                    ->concat("\t\t")
                    ->concat('$this->')
                    ->concat($normalize_field)
                    ->concat(" = $")
                    ->concat($normalize_field)
                    ->concat(";\n\t\t")
                    ->concat('return $this;')
                    ->concat("\n\t}\n")
                    ->concat((count($columns) - 1) === $key ? '' : "\n")
                    ->get()
            );
        }

        $this->add("}\n");
        $this->force();
        $this->close();

        if ($message != null) {
            $output->writeln($this->successOutput("\t>>  CAPSULE: the '{$list['namespace']}\\{$list['class']}' capsule has been generated"));
        }

        return Command::SUCCESS;
    }
}
