<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Selection;

use DI\Attribute\Inject;
use Exception;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Http;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Command class for selecting different types of selection menu
 *
 * @package Lion\Bundle\Helpers\Commands\Selection
 */
class MenuCommand extends Command
{
    /**
     * [InputInterface is the interface implemented by all input classes]
     *
     * @var InputInterface $input
     */
    protected InputInterface $input;

    /**
     * [OutputInterface is the interface implemented by all Output classes]
     *
     * @var OutputInterface $output
     */
    protected OutputInterface $output;

    /**
     * [Modify and build arrays with different indexes or values]
     *
     * @var Arr $arr
     */
    protected Arr $arr;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    protected Store $store;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    protected Str $str;

    #[Inject]
    public function setArr(Arr $arr): MenuCommand
    {
        $this->arr = $arr;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): MenuCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): MenuCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;

        $this->output = $output;
    }

    /**
     * Selection menu to obtain a Vite.JS project
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     *
     * @throws Exception [If there are no projects available]
     *
     * @internal
     */
    protected function selectedProject(InputInterface $input, OutputInterface $output): string
    {
        $projects = [];

        $resources = $this->store->view('resources/');

        if (is_array($resources)) {
            foreach ($resources as $folder) {
                $split = $this->str
                    ->of($folder)
                    ->split('resources/');

                $project = end($split);

                if ($project != '.' && $project != '..') {
                    $projects[] = $project;
                }
            }
        }

        if (empty($projects)) {
            throw new Exception('there are no projects available', Http::INTERNAL_SERVER_ERROR);
        }

        /** @var string $defaultProject */
        $defaultProject = reset($projects);

        if (count($projects) <= 1) {
            $output->writeln($this->warningOutput('(default: ' . $defaultProject . ')'));

            return $defaultProject;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiseQuestion = new ChoiceQuestion(
            ('Select project ' . $this->warningOutput('(default: ' . $defaultProject . ')')),
            $projects,
            0
        );

        /** @var string $response */
        $response = $helper->ask($input, $output, $choiseQuestion);

        return $response;
    }

    /**
     * Open a menu to select a template to create a project with vite
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, string> $templates [List of available templates]
     * @param string $defaultTemplate [Default template]
     * @param int $defaultIndex [Default index]
     *
     * @return string
     *
     * @internal
     */
    protected function selectedTemplate(
        InputInterface $input,
        OutputInterface $output,
        array $templates,
        string $defaultTemplate = 'React',
        int $defaultIndex = 2
    ): string {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiceQuestion = new ChoiceQuestion(
            "Select the type of template {$this->warningOutput("(default: '{$defaultTemplate}')")}",
            $templates,
            $defaultIndex
        );

        /** @var string $template */
        $template = $helper->ask($input, $output, $choiceQuestion);

        return $template;
    }

    /**
     * Selection menu for different types of languages
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, string> $types [description]
     *
     * @return string
     *
     * @internal
     */
    protected function selectedTypes(InputInterface $input, OutputInterface $output, array $types): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiceQuestion = new ChoiceQuestion("Select type {$this->warningOutput("(default: 'js')")}", $types, 0);

        /** @var string $type */
        $type = $helper->ask($input, $output, $choiceQuestion);

        return $type;
    }

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     *
     * @internal
     */
    protected function selectConnection(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $connections = Connection::getConnections();

        $connectionKeys = array_keys($connections);

        /** @var string $defaultConnection */
        $defaultConnection = reset($connectionKeys);

        if ($this->arr->of($connections)->length() > 1) {
            /** @var array<int, string> $choiseConnections */
            $choiseConnections = $this->arr
                ->of($connections)
                ->keys()
                ->get();

            $choiseQuestion = new ChoiceQuestion(
                'Select a connection ' . $this->warningOutput("(default: {$defaultConnection})"),
                $choiseConnections,
                0
            );

            /** @var string $selectedConnection */
            $selectedConnection = $helper->ask($input, $output, $choiseQuestion);
        } else {
            $output->writeln($this->warningOutput("default connection: ({$defaultConnection})"));

            $selectedConnection = $defaultConnection;
        }

        $_ENV['SELECTED_CONNECTION'] = $selectedConnection;

        return $selectedConnection;
    }

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     *
     * @internal
     */
    protected function selectConnectionByEnviroment(InputInterface $input, OutputInterface $output): string
    {
        if (!empty($_ENV['SELECTED_CONNECTION']) && is_string($_ENV['SELECTED_CONNECTION'])) {
            return $_ENV['SELECTED_CONNECTION'];
        }

        return $this->selectConnection($input, $output);
    }

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, string> $options [List of available migration types]
     *
     * @return string
     *
     * @internal
     */
    protected function selectMigrationType(InputInterface $input, OutputInterface $output, array $options): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiceQuestion = new ChoiceQuestion(
            "Select the type of migration {$this->warningOutput('(default: Table)')}",
            $options,
            0
        );

        /** @var string $migrationType */
        $migrationType = $helper->ask($input, $output, $choiceQuestion);

        return $migrationType;
    }

    /**
     * Gets the columns of the entity
     *
     * @param string $driver [Database engine]
     * @param string $selectedConnection [Database connection]
     * @param string $entity [Entity name]
     *
     * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass
     *
     * @internal
     */
    protected function getTableColumns(string $driver, string $selectedConnection, string $entity): array|stdClass
    {
        if (Driver::MYSQL === $driver) {
            return MySQL::connection($selectedConnection)
                ->show()
                ->full()
                ->columns()
                ->from($entity)
                ->getAll();
        }

        if (Driver::POSTGRESQL === $driver) {
            return PostgreSQL::connection($selectedConnection)
                ->query(
                    <<<SQL
                    SELECT
                        a.attname AS "Field",
                        pg_catalog.pg_get_expr(d.adbin, d.adrelid) AS "Default",
                        a.attnotnull AS "Null",
                        COALESCE(pg_catalog.col_description(a.attrelid, a.attnum), '') AS "Comment",
                        pg_catalog.format_type(a.atttypid, a.atttypmod) AS "Type",
                        CASE
                            WHEN pk.constraint_type = 'PRIMARY KEY' THEN 'PRI'
                            WHEN uk.constraint_type = 'UNIQUE' THEN 'UNI'
                            ELSE ''
                        END AS "Key"
                    FROM
                        pg_catalog.pg_attribute a
                        INNER JOIN pg_catalog.pg_class c ON a.attrelid = c.oid
                        INNER JOIN pg_catalog.pg_namespace n ON c.relnamespace = n.oid
                        LEFT JOIN pg_catalog.pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
                        LEFT JOIN (
                            SELECT
                                kcu.column_name,
                                tc.constraint_type
                            FROM
                                information_schema.table_constraints AS tc
                            JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
                            WHERE
                                tc.constraint_type = ?
                                AND tc.table_name = ?
                                AND tc.table_schema = ?
                        ) AS pk ON a.attname = pk.column_name
                        LEFT JOIN (
                            SELECT
                                kcu.column_name,
                                tc.constraint_type
                            FROM
                                information_schema.table_constraints AS tc
                            JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
                            WHERE
                                tc.constraint_type = ?
                                AND tc.table_name = ?
                                AND tc.table_schema = ?
                        ) AS uk ON a.attname = uk.column_name
                    WHERE
                        c.relname = ?
                        AND n.nspname = ?
                        AND a.attnum > ?
                        AND NOT a.attisdropped
                    ORDER BY
                        a.attnum;
                    SQL
                )
                ->addRows([
                    'PRIMARY KEY',
                    $entity,
                    'public',
                    'UNIQUE',
                    $entity,
                    'public',
                    $entity,
                    'public',
                    0
                ])
                ->getAll();
        }

        return [];
    }

    /**
     * Get the foreign keys of a table
     *
     * @param string $driver [Database engine]
     * @param string $connectionName [Database connection]
     * @param string $databaseName [Database name]
     * @param string $entity [Entity name]
     *
     * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass
     *
     * @internal
     */
    protected function getTableForeigns(
        string $driver,
        string $connectionName,
        string $databaseName,
        string $entity
    ): array|stdClass {
        if (Driver::MYSQL === $driver) {
            return MySQL::connection($connectionName)
                ->table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE', false)
                ->select('COLUMN_NAME')
                ->where()->equalTo('TABLE_SCHEMA', $databaseName)
                ->and()->equalTo('TABLE_NAME', $entity)
                ->and('REFERENCED_TABLE_NAME')->isNotNull()
                ->getAll();
        }

        if (Driver::POSTGRESQL === $driver) {
            return PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    SELECT
                        kcu.column_name AS "COLUMN_NAME"
                    FROM information_schema.table_constraints AS tc
                        JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
                        JOIN information_schema.constraint_column_usage AS ccu
                            ON ccu.constraint_name = tc.constraint_name
                    WHERE tc.constraint_type = ?
                    AND tc.table_schema = ?
                    AND tc.table_name = ?
                    SQL
                )
                ->addRows(['FOREIGN KEY', 'public', $entity])
                ->getAll();
        }

        return [];
    }

    /**
     * Gets the available tables
     *
     * @param string $connectionName [Connection name]
     *
     * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass
     */
    protected function getTables(string $connectionName): array|stdClass
    {
        $connections = Connection::getConnections();

        if (empty($connections[$connectionName])) {
            return [];
        }

        if (Driver::MYSQL === $connections[$connectionName]['type']) {
            return MySQL::connection($connectionName)
                ->show()
                ->tables()
                ->getAll();
        }

        if (Driver::POSTGRESQL === $connections[$connectionName]['type']) {
            return PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    SELECT
                        tablename,
                        tablename AS "Tables_in_{$connections[$connectionName]['dbname']}"
                    FROM pg_tables
                    WHERE schemaname = 'public';
                    SQL
                )
                ->getAll();
        }

        return [];
    }
}
