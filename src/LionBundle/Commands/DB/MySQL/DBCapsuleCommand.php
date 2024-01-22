<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\DB\MySQL;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\SelectedDatabaseConnection;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Helpers\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DBCapsuleCommand extends SelectedDatabaseConnection
{
    private ClassFactory $classFactory;
    private Str $str;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): DBCapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): DBCapsuleCommand
    {
        $this->str = $str;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('db:mysql:capsule')
            ->setDescription('Command required for the creation of new Capsules')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');
        $selectedConnection = $this->selectConnectionByEnviroment($input, $output);

        $entity = $this->str->of($entity)->test("/-/") ? "`{$entity}`" : $entity;
        $columns = DB::connection($selectedConnection)->show()->columns()->from($entity)->getAll();
        $columns = count($columns) > 1 ? $columns : reset($columns);

        if (!empty($columns->status)) {
            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$columns->message}"));

            return Command::FAILURE;
        }

        $propierties = [];

        foreach ($columns as $column) {
            $propierties[] = "{$column->Field}:{$this->classFactory->getDBType($column->Type)}";
        }

        $connPascal = $this->classFactory->getClassFormat($selectedConnection);
        $className = $this->classFactory->getClassFormat($this->str->of($entity)->replace('`', '')->get());

        $this->getApplication()
            ->find('new:capsule')
            ->run(
                new ArrayInput(['capsule' => "{$connPascal}/MySQL/{$className}", '--propierties' => $propierties]),
                $output
            );

        return Command::SUCCESS;
    }
}
