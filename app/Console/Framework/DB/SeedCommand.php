<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class SeedCommand extends Command {

    use ClassPath;

	protected static $defaultName = "db:seed";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command required for creating new seeds")
            ->addArgument('seed', InputArgument::OPTIONAL, 'Name or namespace of the Seed', 'ExampleSeed')
            ->addOption('run', 'r', InputOption::VALUE_REQUIRED, 'Do you want to run the seeder?');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $seed = $input->getArgument('seed');
        $run = $input->getOption('run');

        if (empty($run)) {
            $run = false;
        }

        $run = $run === 'true' ? true : false;
        $output->writeln("<comment>\t>>  SEED: {$seed}</comment>");

        if (!$run) {
            $list = $this->export("database/Seeders/", $seed);
            $url_folder = lcfirst(str->of($list['namespace'])->replace("\\", "/")->get());
            Store::folder($url_folder);

            $this->create($url_folder, $list['class']);
            $this->add(str->of("<?php")->ln()->ln()->get());
            $this->add(str->of("namespace ")->concat($list['namespace'])->concat(";")->ln()->ln()->get());
            $this->add(str->of("use LionSQL\Drivers\MySQL\MySQL as DB;")->ln()->get());
            $this->add(str->of("use LionSQL\Drivers\MySQL\Schema;")->ln()->ln()->get());
            $this->add(str->of("class ")->concat($list['class'])->concat(" {")->ln()->ln()->get());
            $this->add("\t/**\n");
            $this->add("\t * ------------------------------------------------------------------------------\n");
            $this->add("\t * Seed the application's database\n");
            $this->add("\t * ------------------------------------------------------------------------------\n");
            $this->add("\t **/\n");
            $this->add("\tpublic function run(): array|object {\n\t\treturn DB::call('stored_procedure', [])->execute();\n\t}\n\n}");
            $this->force();
            $this->close();

            $output->writeln("<info>\t>>  SEED: The '{$list['namespace']}\\{$list['class']}' seed has been generated</info>");
            return Command::SUCCESS;
        }

        $namespace = str->of($seed)->replace("/", "\\")->get();
        if (!class_exists($namespace)) {
            $output->writeln("<fg=#E37820>\t>>  SEED: Class does not exist</>");
            return Command::INVALID;
        }

        $res = (new $namespace())->run();
        if (!isset($res->status)) {
            (new Table($output))->setHeaders($res['columns'])->setRows($res['rows'])->render();
        } else {
            if (isError($res)) {
                $output->writeln("<fg=#E37820>\t>>  SEED: {$res->message}</>");
                return Command::INVALID;
            }

            $output->writeln("<info>{$res->message}</info>");
        }

        return Command::SUCCESS;
    }

}
