<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreshMigrationsCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "migrate:fresh";
    private array $connections;
    private array $files = [];

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->connections = DB::getConnections();
        $folders = ["Tables", "Views", "Procedures"];

        foreach ($folders as $key => $folder) {
            foreach (arr->of($this->connections['connections'])->keys()->get() as $key => $connection) {
                $db_pascal = str->of($connection)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();

                if (isSuccess(Store::exist("database/Migrations/{$db_pascal}/{$folder}/"))) {
                    foreach (Store::view("database/Migrations/{$db_pascal}/{$folder}/") as $key => $file) {
                        if (isSuccess(Store::validate([$file], ["php"]))) {
                            $class = require_once($file);
                            $info = $class->getMigration();
                            $type = str->of($info['type'])->lower()->get();

                            if ($type === "table") {
                                $this->files[$info['connection']]["tables"][] = [
                                    'file' => $file,
                                    'index' => $info['index'],
                                    'class' => $class
                                ];
                            } else {
                                $this->files[$info['connection']][
                                    ($type === "view" ? "views" : "procedures")
                                ][] = [
                                    'file' => $file,
                                    'class' => $class
                                ];
                            }
                        }
                    }
                }

                DB::connection($connection)
                    ->query("USE `{$connection}`;")
                    ->query("SET FOREIGN_KEY_CHECKS = 0;")
                    ->query("SET @tablas = NULL;")
                    ->query("SELECT GROUP_CONCAT(table_name) INTO @tablas FROM information_schema.tables WHERE table_schema = (SELECT DATABASE());")
                    ->query("SET @consulta = CONCAT('DROP TABLE IF EXISTS ', @tablas);")
                    ->query("PREPARE stmt FROM @consulta;")
                    ->query("EXECUTE stmt;")
                    ->query("DEALLOCATE PREPARE stmt;")
                    ->query("SET FOREIGN_KEY_CHECKS = 1;")
                    ->execute();
            }
        }

        foreach (arr->of($this->files)->keys()->get() as $index => $key) {
            if (isset($this->files[$key]['tables'])) {
                usort($this->files[$key]['tables'], function($a, $b) {
                    if ($a['index'] === null && $b['index'] === null) {
                        return 0;
                    } elseif ($a['index'] === null) {
                        return 1;
                    } elseif ($b['index'] === null) {
                        return -1;
                    } else {
                        return $a['index'] - $b['index'];
                    }
                });
            }
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Drop all tables and re-run all migrations");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $items = arr->of($this->files)->keys()->get();

        foreach ($items as $indexFiles => $keyFiles) {
            $output->writeln($this->warningOutput("\t>>  DATABASE: {$keyFiles}"));

            if (isset($this->files[$keyFiles]["tables"])) {
                foreach ($this->files[$keyFiles]["tables"] as $key => $class) {
                    $info = $class['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($class['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->replace("tables/", "Tables/")->get();

                    $res = $class['class']->execute();
                    if (isError($res)) {
                        $output->writeln($this->errorOutput("\t>>  TABLE: An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}"));
                    } else {
                        $output->writeln($this->successOutput("\t>>  TABLE: Migration '{$migration}' has been executed \u{2713}"));
                    }

                    // execute insert function process
                    $data = $class['class']->insert();
                    if (arr->of($data['rows'])->length() > 0) {
                        $res = DB::connection($info['connection'])->table($info['table'])->bulk($data['columns'], $data['rows'])->execute();
                        if (isError($res)) {
                            $output->writeln($this->errorOutput("\t>>  BULKING: Error executing bulk migration of '{$migration}', {$res->message} \u{2717}"));
                        } else {
                            $output->writeln($this->infoOutput("\t>>  BULKING: Insert function of '{$migration}' migration executed correctly \u{2713}"));
                        }
                    }
                }
            }

            if (isset($this->files[$keyFiles]["views"])) {
                foreach ($this->files[$keyFiles]["views"] as $key => $view) {
                    $info = $view['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($view['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->replace("views/", "Views/")->get();

                    // execute function process execute
                    $res = $view['class']->execute();
                    if (isError($res)) {
                        $output->writeln($this->errorOutput("\t>>  VIEW: An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}"));
                    } else {
                        $output->writeln($this->successOutput("\t>>  VIEW: Migration '{$migration}' has been executed \u{2713}"));
                    }
                }
            }

            if (isset($this->files[$keyFiles]["procedures"])) {
                foreach ($this->files[$keyFiles]["procedures"] as $key => $procedure) {
                    $info = $procedure['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($procedure['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->replace("procedures/", "Procedures/")->get();

                    // execute function process execute
                    $res = $procedure['class']->execute();
                    if (isError($res)) {
                        $output->writeln($this->errorOutput("\t>>  PROCEDURE: An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}"));
                    } else {
                        $output->writeln($this->successOutput("\t>>  PROCEDURE: Migration '{$migration}' has been executed \u{2713}"));
                    }

                    // execute insert function process
                    $data = $procedure['class']->insert();
                    foreach ($data['rows'] as $keyRow => $row) {
                        $res = DB::connection($info['connection'])->call($info['procedure'], $row)->execute();
                        if (isError($res)) {
                            $output->writeln($this->errorOutput("\t>>  INSERT: An error occurred while executing the insert function of '{$migration}' migration, {$res->message} \u{2717}"));
                        } else {
                            $output->writeln($this->infoOutput("\t>>  INSERT: Insert function of '{$migration}' migration executed correctly \u{2713}"));
                        }
                    }
                }
            }

            if ($indexFiles < (count($items) - 1)) {
                $output->writeln("");
            }
        }

        return Command::SUCCESS;
    }
}
