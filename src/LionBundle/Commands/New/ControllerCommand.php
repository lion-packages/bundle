<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends Command
{
    const METHODS = ['create', 'read', 'update', 'delete'];

    private ClassFactory $classFactory;
    private ClassFactory $classFactoryModel;
    private Store $store;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->classFactory = new ClassFactory();
        $this->classFactoryModel = new ClassFactory();
        $this->store = new Store();
    }

    protected function configure(): void
    {
        $this
            ->setName('new:controller')
            ->setDescription('Command required for the creation of new Controllers')
            ->addArgument('controller', InputArgument::OPTIONAL, 'Controller name', 'ExampleController')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Do you want to create the model?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $controller = $input->getArgument('controller');
        $model = $input->getOption('model');

        $this->classFactory->classFactory('app/Http/Controllers/', $controller);
        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $camelModelClass = "";
        $dataModel = [];

        if ($model != null) {
            $this->classFactoryModel->classFactory('app/models/', $model);
            $dataModel['folder'] = $this->classFactoryModel->getFolder();
            $dataModel['class'] = $this->classFactoryModel->getClass();
            $dataModel['namespace'] = $this->classFactoryModel->getNamespace();
        }

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n");

        if ($model != null) {
            $this->classFactory->add("use {$dataModel['namespace']}\\{$dataModel['class']};\n\n");
        }

        $this->classFactory->add("class {$class}\n{\n");

        if ($model != null) {
            $camelModelClass = Str::of(lcfirst($dataModel['class']))->trim()->get();

            $this->classFactory->add(
                Str::of("\tprivate {$dataModel['class']} $")
                    ->concat($camelModelClass)
                    ->concat(";")->ln()->ln()
                    ->get()
            );

            $this->classFactory->add(
                $this->classFactory->getCustomMethod(
                    '__construct',
                    '',
                    '',
                    '$this->' . "{$camelModelClass} = new {$dataModel['class']}();",
                    'public'
                )
            );
        } else {
            $this->classFactory->add($this->classFactory->getCustomMethod('__construct', '', '', '', 'public'));
        }

        foreach (self::METHODS as $key => $method) {
            $customMethod = '';

            if ($model != null) {
                $modelMethod = Str::of('return ')->concat('$this->')->concat($camelModelClass)->concat('->')->get();

                $modelMethod .= Str::of($method . $dataModel['class'])
                    ->replace('Model', '')
                    ->replace('model', '')
                    ->concat('DB();')
                    ->get();

                $customMethod = $this->classFactory->getCustomMethod(
                    Str::of($method . $class)->replace('Controller', '')->replace('controller', '')->get(),
                    $method === 'read' ? 'array|object' : 'object',
                    in_array($method, ['update', 'delete'], true) ? 'string $id' : '',
                    $modelMethod,
                    'public',
                    $method === 'delete' ? 1 : 2
                );
            } else {
                $customMethod = $this->classFactory->getCustomMethod(
                    Str::of($method . $class)->replace('Controller', '')->replace('controller', '')->get(),
                    $method === 'read' ? 'array|object' : 'object',
                    in_array($method, ['update', 'delete'], true) ? 'string $id' : '',
                    $method === 'read' ? "return [];" : "return success();",
                    'public',
                    $method === 'delete' ? 1 : 2
                );
            }

            $this->classFactory->add($customMethod);
        }

        $this->classFactory->add("}\n")->close();

        $output->writeln($this->warningOutput("\t>>  CONTROLLER: {$controller}"));

        $output->writeln(
            $this->successOutput("\t>>  CONTROLLER: the '{$namespace}\\{$class}' controller has been generated")
        );

        if ($model != null) {
            $this->getApplication()->find('new:model')->run(new ArrayInput(['model' => $model]), $output);
        }

        return Command::SUCCESS;
    }
}
