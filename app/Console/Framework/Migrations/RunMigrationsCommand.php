<?php

namespace App\Console\Framework\Migrations;

use LionFiles\Store;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunMigrationsCommand extends Command {

	protected static $defaultName = "migrate:fresh";
    private array $connections;
    private array $files = [];

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->connections = DB::getConnections();

        foreach (arr->of($this->connections['connections'])->keys()->get() as $key => $connection) {
            if (isSuccess(Store::exist("database/Migrations/{$connection}/"))) {
                foreach (Store::view("database/Migrations/{$connection}/") as $key => $file) {
                    if (isSuccess(Store::validate([$file], ["php"]))) {
                        $class = require_once($file);
                        $info = $class->getMigration();
                        $type = str->of($info['type'])->lower()->get();

                        if ($type === "table") {
                            $this->files[$info['connection']][$type][] = [
                                'file' => $file,
                                'index' => $info['index'],
                                'class' => $class
                            ];
                        } else {
                            $this->files[$info['connection']][$type][] = [
                                'file' => $file,
                                'class' => $class
                            ];
                        }
                    }
                }
            }
        }

        foreach (arr->of($this->files)->keys()->get() as $index => $key) {
            usort($this->files[$key]['table'], function($a, $b) {
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

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Drop all tables and re-run all migrations");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $rows = [];

        foreach (arr->of($this->files)->keys()->get() as $indexFiles => $keyFiles) {
            foreach ($this->files[$keyFiles] as $key => $file) {
                foreach ($file as $keyFile => $class) {
                    $info = $class['class']->getMigration();
                    $migration = str->of($class['file'])->replace("database/Migrations/{$info['connection']}/", "")->replace(".php", "")->get();

                    if ($key === "table") {
                        // execute function process execute
                        $res = $class['class']->execute();
                        $rows[] = [
                            "<fg=#FFB63E>{$info['connection']}</>",
                            $migration,
                            "<fg=#FFB63E>" . str->of($key)->upper()->get() . "</>",
                            '<fg=#FFB63E>execute</>',
                            $res->status === "success" ? "<info>success</info>" : "<fg=#E37820>database-error</>",
                            $res->message
                        ];

                        // execute insert function process
                        $data = $class['class']->insert();
                        if (arr->of($data['rows'])->length() > 0) {
                            $res = DB::connection($info['connection'])->table($info['table'])->bulk($data['columns'], $data['rows'])->execute();

                            $rows[] = [
                                "<fg=#FFB63E>{$info['connection']}</>",
                                $migration,
                                "<fg=#FFB63E>" . str->of("BULK {$key}")->upper()->get() . "</>",
                                '<fg=#FFB63E>insert</>',
                                $res->status === "success" ? "<info>success</info>" : "<fg=#E37820>database-error</>",
                                $res->message
                            ];
                        }

                        if (isset($this->files[$keyFiles]["view"])) {
                            if (arr->of($this->files[$keyFiles]["view"])->length() > 0) {
                                $rows[] = new TableSeparator();
                            }
                        }
                    }

                    if ($key === "view") {
                        // execute function process execute
                        $res = $class['class']->execute();
                        $rows[] = [
                            "<fg=#FFB63E>{$info['connection']}</>",
                            $migration,
                            "<fg=#FFB63E>" . str->of($key)->upper()->get() . "</>",
                            '<fg=#FFB63E>execute</>',
                            $res->status === "success" ? "<info>success</info>" : "<fg=#E37820>database-error</>",
                            $res->message
                        ];

                        if (isset($this->files[$keyFiles]["procedure"])) {
                            if (arr->of($this->files["procedure"])->length() > 0) {
                                $rows[] = new TableSeparator();
                            }
                        }
                    }

                    if ($key === "procedure") {
                        // execute function process execute
                        $res = $class['class']->execute();
                        $rows[] = [
                            "<fg=#FFB63E>{$info['connection']}</>",
                            $migration,
                            "<fg=#FFB63E>" . str->of($key)->upper()->get() . "</>",
                            '<fg=#FFB63E>execute</>',
                            $res->status === "success" ? "<info>success</info>" : "<fg=#E37820>database-error</>",
                            $res->message
                        ];

                        // execute insert function process
                        $data = $class['class']->insert();
                        foreach ($data['rows'] as $keyRow => $row) {
                            $res = DB::connection($info['connection'])->call($info['procedure'], $row)->execute();

                            $rows[] = [
                                "<fg=#FFB63E>{$info['connection']}</>",
                                $migration,
                                "<fg=#FFB63E>" . str->of("INSERT {$key}")->upper()->get() . "</>",
                                '<fg=#FFB63E>insert</>',
                                $res->status === "success" ? "<info>success</info>" : "<fg=#E37820>database-error</>",
                                $res->message
                            ];
                        }
                    }
                }
            }
        }

        (new Table($output))
            ->setHeaderTitle('<info> MIGRATIONS </info>')
            ->setHeaders(['DATABASE', 'MIGRATION', 'TYPE', 'METHOD', 'STATUS', 'MESSAGE'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

}
