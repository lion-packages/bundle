<?php

declare(strict_types=1);

namespace LionBundle\Commands\DB\MySQL;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionHelpers\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DBCapsuleCommand extends Command
{
    private ClassFactory $classFactory;
    private array $connections;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->classFactory = new ClassFactory();
    }

    protected function configure(): void
    {
        $this->connections = DB::getConnections();

        $this
            ->setName('db:mysql:capsule')
            ->setDescription('Command required for the creation of new Capsules')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name', null)
            ->addOption(
                'connection',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Do you want to use a specific connection?',
                $this->connections['default']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = $input->getArgument('entity');
        $connection = $input->getOption('connection');

        $entity = Str::of($entity)->test("/-/") ? "`{$entity}`" : $entity;
        $columns = DB::connection($connection)->show()->columns()->from($entity)->getAll();
        $columns = count($columns) > 1 ? $columns : reset($columns);

        if (!empty($columns->status)) {
            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$columns->message}"));

            return Command::FAILURE;
        }

        $propierties = [];

        foreach ($columns as $column) {
            $propierties[] = "{$column->Field}:{$this->classFactory->getDBType($column->Type)}";
        }

        $connPascal = $this->classFactory->getClassFormat($connection);
        $className = $this->classFactory->getClassFormat(Str::of($entity)->replace('`', '')->get());

        $this->getApplication()
            ->find('new:capsule')
            ->run(
                new ArrayInput(['capsule' => "{$connPascal}/MySQL/{$className}", '--propierties' => $propierties]),
                $output
            );

        return Command::SUCCESS;
    }
}
