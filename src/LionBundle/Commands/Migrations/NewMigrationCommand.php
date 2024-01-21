<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Migrations;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migration;
use Lion\Bundle\Helpers\Commands\SelectedDatabaseConnection;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NewMigrationCommand extends SelectedDatabaseConnection
{
    const OPTIONS = ['Table', 'View', 'Store-Procedure'];

    private ClassFactory $classFactory;
    private Store $store;
    private Str $str;
    private Migration $migration;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): NewMigrationCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): NewMigrationCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): NewMigrationCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * @required
     * */
    public function setMigration(Migration $migration): NewMigrationCommand
    {
        $this->migration = $migration;

        return $this;
    }

	protected function configure(): void
    {
		$this
            ->setName('migrate:new')
            ->setDescription('Command to generate a new migration')
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migration = $input->getArgument('migration');

        if (str->of($migration)->test("/.*\//")) {
            $output->writeln($this->errorOutput("\t>>  migration cannot be inside subfolders"));

            return Command::INVALID;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $selectedConnection = $this->selectConnection($input, $output, $helper);

        $selectedType = $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                'Select the type of migration ' . $this->warningOutput('(default: Table)'),
                self::OPTIONS,
                0
            )
        );

        $migrationPascal = $this->str->of($migration)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
        $dbPascal = $this->str->of($selectedConnection)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();

        if ('Table' === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/Tables/");
            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/Tables/", $migrationPascal);
            $body = $this->migration->getTableBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), 'php', $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $selectedConnection)->get())
                ->close();
        }

        if ('View' === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/Views/");
            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/Views/", $migrationPascal);
            $body = $this->migration->getViewBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), 'php', $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $selectedConnection)->get())
                ->close();
        }

        if ('Store-Procedure' === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/StoreProcedures/");
            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/StoreProcedures/", $migrationPascal);
            $body = $this->migration->getStoreProcedureBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), 'php', $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $selectedConnection)->get())
                ->close();
        }

        $output->writeln($this->warningOutput("\t>>  MIGRATION: {$this->classFactory->getClass()}"));

        $output->writeln(
            $this->successOutput(
                "\t>>  MIGRATION: the '{$this->classFactory->getNamespace()}\\{$this->classFactory->getClass()}' migration has been generated"
            )
        );

		return Command::SUCCESS;
	}
}
