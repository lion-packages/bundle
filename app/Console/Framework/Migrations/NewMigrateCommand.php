<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NewMigrateCommand extends Command {

	protected static $defaultName = "migrate:new";
    private array $options = ["TABLE", 'VIEW', 'PROCEDURE'];

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to generate a new migration")
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        // get migration name and validate that it is not in subfolders
        $migration = $input->getArgument('migration');
        if (str->of($migration)->test("/.*\//")) {
            $output->writeln("<error>Migration cannot be inside subfolders</error>");
            return Command::INVALID;
        }

        // select type of migration
        $option = $this->getHelper('question')->ask(
            $input,
            $output,
            (new ChoiceQuestion("What type of migration do you want to create?", $this->options, 0))
                ->setErrorMessage('The selected option is not valid')
        );

        // select connection
        $connections = DB::getConnections();
        $connection = $this->getHelper('question')->ask(
            $input,
            $output,
            (new ChoiceQuestion(
                "Which connection does the migration belong to?",
                arr->of($connections['connections'])->keys()->get(),
                0
            ))->setErrorMessage('The selected option is not valid')
        );

        Store::folder("database/Migrations/{$connection}/");
        $migration = str->of("database/Migrations/")
            ->concat("{$connection}/")
            ->concat($migration)
            ->replace("-", "_")
            ->replace(" ", "_")
            ->lower()
            ->trim()
            ->get();
		ClassPath::new($migration, "php");

        if ($option === "TABLE") {
            ClassPath::add(ClassPath::getTemplateCreateTable());
        } elseif ($option === "VIEW") {
            ClassPath::add(ClassPath::getTemplateCreateView());
        } elseif ($option === "PROCEDURE") {
            ClassPath::add(ClassPath::getTemplateCreateProcedure());
        }

        ClassPath::force();
        ClassPath::close();

		return Command::SUCCESS;
	}

}
