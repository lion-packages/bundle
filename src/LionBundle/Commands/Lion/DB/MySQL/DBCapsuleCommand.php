<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB\MySQL;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBCapsuleCommand extends MenuCommand
{
    private ClassFactory $classFactory;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): DBCapsuleCommand
    {
        $this->classFactory = $classFactory;

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

        if (!empty($columns->status)) {
            $output->writeln($this->errorOutput("\t>>  CAPSULE: {$columns->message}"));

            return Command::FAILURE;
        }

        $properties = [];

        foreach ($columns as $column) {
            $properties[] = "{$column->Field}:{$this->classFactory->getDBType($column->Type)}";
        }

        $connPascal = $this->classFactory->getClassFormat($selectedConnection);
        $className = $this->classFactory->getClassFormat($this->str->of($entity)->replace('`', '')->get());

        $this->getApplication()
            ->find('new:capsule')
            ->run(
                new ArrayInput(['capsule' => "{$connPascal}/MySQL/{$className}", '--properties' => $properties]),
                $output
            );

        return Command::SUCCESS;
    }
}
