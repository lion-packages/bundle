<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\Env;
use Lion\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a migration for database structure control
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property MigrationFactory $migrationFactory [MigrationFactory class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class MigrationCommand extends MenuCommand
{
    /**
     * [Constant for a table]
     *
     * @const TABLE
     */
    const TABLE = 'Table';

    /**
     * [Constant for a view]
     *
     * @const VIEW
     */
    const VIEW = 'View';

    /**
     * [Constant for a store-procedure]
     *
     * @const STORE_PROCEDURE
     */
    const STORE_PROCEDURE = 'Store-Procedure';

    /**
     * [List of available types]
     *
     * @const OPTIONS
     */
    const OPTIONS = [self::TABLE, self::VIEW, self::STORE_PROCEDURE];

    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [MigrationFactory class object]
     *
     * @var MigrationFactory $migrationFactory
     */
    private MigrationFactory $migrationFactory;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): MigrationCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setMigration(MigrationFactory $migrationFactory): MigrationCommand
    {
        $this->migrationFactory = $migrationFactory;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
	protected function configure(): void
    {
		$this
            ->setName('new:migration')
            ->setDescription('Command required to generate a new migration')
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name');
	}

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migration = $input->getArgument('migration');

        if (str->of($migration)->test("/.*\//")) {
            $output->writeln($this->errorOutput("\t>>  migration cannot be inside subfolders"));

            return Command::INVALID;
        }

        $selectedConnection = $this->selectConnection($input, $output);

        $selectedType = $this->selectMigrationType($input, $output, self::OPTIONS);

        $migrationPascal = $this->str->of($migration)->replace('-', ' ')->replace('_', ' ')->pascal()->trim()->get();

        $dbPascal = $this->str->of($selectedConnection)->replace('-', ' ')->replace('_', ' ')->pascal()->trim()->get();

        $envName = Env::getKey($selectedConnection);

        if (self::TABLE === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/Tables/");

            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/Tables/", $migrationPascal);

            $body = $this->migrationFactory->getTableBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), ClassFactory::PHP_EXTENSION, $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $envName)->get())
                ->close();
        }

        if (self::VIEW === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/Views/");

            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/Views/", $migrationPascal);

            $body = $this->migrationFactory->getViewBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), ClassFactory::PHP_EXTENSION, $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $envName)->get())
                ->close();
        }

        if (self::STORE_PROCEDURE === $selectedType) {
            $this->store->folder("database/Migrations/{$dbPascal}/StoreProcedures/");

            $this->classFactory->classFactory("database/Migrations/{$dbPascal}/StoreProcedures/", $migrationPascal);

            $body = $this->migrationFactory->getStoreProcedureBody();

            $this->classFactory
                ->create($this->classFactory->getClass(), ClassFactory::PHP_EXTENSION, $this->classFactory->getFolder())
                ->add($this->str->of($body)->replace('--CONNECTION--', $envName)->get())
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
