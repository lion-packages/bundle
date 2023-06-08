<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ClassPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NewMigrateCommand extends Command {

	protected static $defaultName = "migrate:new";
    private array $options = ["TABLE", 'VIEW', 'PROCEDURE'];
    private string $option;
    private string $migration;

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
        $this->migration = $input->getArgument('migration');
        if (str->of($this->migration)->test("/.*\//")) {
            $output->writeln("<error>Migration cannot be inside subfolders</error>");
            return Command::INVALID;
        }

        // select type of migration
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("What type of migration do you want to create?", $this->options, 0);
        $question->setErrorMessage('The selected option is not valid');
        $this->option = $helper->ask($input, $output, $question);

        $migration = str->of("database/Migrations/")
            ->concat($this->migration)
            ->replace("-", "_")
            ->replace(" ", "_")
            ->lower()
            ->trim()
            ->get();
		ClassPath::new($migration, "php");

        if ($this->option === "TABLE") {
            ClassPath::add(ClassPath::getTemplateCreateTable());
        } elseif ($this->option === "VIEW") {
            ClassPath::add(ClassPath::getTemplateCreateView());
        } elseif ($this->option === "PROCEDURE") {
            ClassPath::add(ClassPath::getTemplateCreateProcedure());
        }

        ClassPath::force();
        ClassPath::close();

		return Command::SUCCESS;
	}

}
