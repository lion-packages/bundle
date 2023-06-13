<?php

namespace App\Console\Framework\Migrations;

use LionFiles\Store;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreshMigrationsCommand extends Command {

	protected static $defaultName = "migrate:fresh";
    private array $connections;
    private array $files = [];

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->connections = DB::getConnections();

        foreach (arr->of($this->connections['connections'])->keys()->get() as $key => $connection) {
            $db_pascal = str->of($connection)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();

            if (isSuccess(Store::exist("database/Migrations/{$db_pascal}/"))) {
                foreach (Store::view("database/Migrations/{$db_pascal}/") as $key => $file) {
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

            DB::connection($connection)->query("USE `{$connection}`; SET FOREIGN_KEY_CHECKS = 0; SET @tablas = NULL; SELECT GROUP_CONCAT(table_name) INTO @tablas FROM information_schema.tables WHERE table_schema = (SELECT DATABASE()); SET @consulta = CONCAT('DROP TABLE IF EXISTS ', @tablas); PREPARE stmt FROM @consulta; EXECUTE stmt; DEALLOCATE PREPARE stmt; SET FOREIGN_KEY_CHECKS = 1;")->execute();
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

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Drop all tables and re-run all migrations");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $items = arr->of($this->files)->keys()->get();

        foreach ($items as $indexFiles => $keyFiles) {
            $output->write("\033[1;33m");
            $output->write("\t>>");
            $output->write("\033[0m");
            $output->writeln("  <comment>DATABASE: {$keyFiles}</comment>");

            if (isset($this->files[$keyFiles]["tables"])) {
                foreach ($this->files[$keyFiles]["tables"] as $key => $class) {
                    $info = $class['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($class['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->get();

                    $res = $class['class']->execute();
                    $output->write("\033[1;33m");
                    $output->write("\t>>");
                    $output->write("\033[0m");

                    if (isError($res)) {
                        $output->writeln("  TABLE: <fg=#E37820>An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}</>");
                    } else {
                        $output->writeln("  TABLE: <info>Migration '{$migration}' has been executed \u{2713}</info>");
                    }

                    // execute insert function process
                    $data = $class['class']->insert();
                    if (arr->of($data['rows'])->length() > 0) {
                        $res = DB::connection($info['connection'])->table($info['table'])->bulk($data['columns'], $data['rows'])->execute();
                        $output->write("\033[1;33m");
                        $output->write("\t>>");
                        $output->write("\033[0m");

                        if (isError($res)) {
                            $output->writeln("  BULKING: <fg=#E37820>Error executing bulk migration of '{$migration}', {$res->message} \u{2717}</>");
                        } else {
                            $output->writeln("  BULKING: <info>Insert function of '{$migration}' migration executed correctly \u{2713}</info>");
                        }
                    }
                }
            }

            if (isset($this->files[$keyFiles]["views"])) {
                foreach ($this->files[$keyFiles]["views"] as $key => $view) {
                    $info = $view['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($view['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->get();

                    // execute function process execute
                    $res = $view['class']->execute();
                    $output->write("\033[1;33m");
                    $output->write("\t>>");
                    $output->write("\033[0m");

                    if (isError($res)) {
                        $output->writeln("  VIEW: <fg=#E37820>An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}</>");
                    } else {
                        $output->writeln("  VIEW: <info>Migration '{$migration}' has been executed \u{2713}</info>");
                    }
                }
            }

            if (isset($this->files[$keyFiles]["procedures"])) {
                foreach ($this->files[$keyFiles]["procedures"] as $key => $procedure) {
                    $info = $procedure['class']->getMigration();
                    $db_pascal = str->of($info['connection'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                    $migration = str->of($procedure['file'])->replace("database/Migrations/{$db_pascal}/", "")->replace(".php", "")->get();

                    // execute function process execute
                    $res = $procedure['class']->execute();
                    $output->write("\033[1;33m");
                    $output->write("\t>>");
                    $output->write("\033[0m");

                    if (isError($res)) {
                        $output->writeln("  PROCEDURE: <fg=#E37820>An error occurred while executing the '{$migration}' migration, {$res->message} \u{2717}</>");
                    } else {
                        $output->writeln("  PROCEDURE: <info>Migration '{$migration}' has been executed \u{2713}</info>");
                    }

                    // execute insert function process
                    $data = $procedure['class']->insert();
                    foreach ($data['rows'] as $keyRow => $row) {
                        $res = DB::connection($info['connection'])->call($info['procedure'], $row)->execute();
                        $output->write("\033[1;33m");
                        $output->write("\t>>");
                        $output->write("\033[0m");

                        if (isError($res)) {
                            $output->writeln("  INSERT: <fg=#E37820>An error occurred while executing the insert function of '{$migration}' migration, {$res->message} \u{2717}</>");
                        } else {
                            $output->writeln("  INSERT: <info>Insert function of '{$migration}' migration executed correctly \u{2713}</info>");
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
