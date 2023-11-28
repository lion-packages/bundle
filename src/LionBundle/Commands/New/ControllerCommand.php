<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassCommandFactory;
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
    const PATH_CONTROLLER = 'app/Http/Controllers/';
    const PATH_MODEL = 'app/models/';

    protected function configure(): void
    {
        $this
            ->setName('new:controller')
            ->setDescription('Command required for the creation of new Controllers')
            ->addArgument('controller', InputArgument::OPTIONAL, 'Controller name', 'ExampleController')
            ->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to create the model?', 'none');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory = (new ClassCommandFactory(['controller', 'model']));

        return $factory->execute(function(ClassCommandFactory $classFactory, Store $store) use ($input, $output) {
            $controller = $input->getArgument('controller');
            $model = $input->getOption('model');

            if (null === $model) {
                $model = Str::of($model)
                    ->concat($controller)
                    ->replace('Controller', '')
                    ->replace('controller', '')
                    ->concat('Model')
                    ->get();
            }

            $factoryController = $classFactory->getFactory('controller');
            $factoryModel = $classFactory->getFactory('model');

            $dataController = $classFactory->getData($factoryController, [
                'path' => self::PATH_CONTROLLER,
                'class' => $controller
            ]);

            $dataModel = $classFactory->getData($factoryModel, [
                'path' => self::PATH_MODEL,
                'class' => $model
            ]);

            $camelModelClass = lcfirst($dataModel->class);
            $store->folder($dataController->folder);

            $factoryController
                ->create($dataController->class, 'php', $dataController->folder)
                ->add("<?php\n\ndeclare(strict_types=1);\n\n")
                ->add("namespace {$dataController->namespace};\n\n");

            if ('none' != $model) {
                $factoryController->add("use {$dataModel->namespace}\\{$dataModel->class};\n\n");
            }

            $factoryController->add("class {$dataController->class}\n{\n");

            if ('none' != $model) {
                $factoryController->add(
                    Str::of("\tprivate {$dataModel->class} $")
                        ->concat($camelModelClass)
                        ->concat(";")->ln()->ln()
                        ->get()
                );

                $factoryController->add(
                    $factoryController->getCustomMethod(
                        '__construct',
                        '',
                        '',
                        '$this->' . "{$camelModelClass} = new {$dataModel->class}();",
                        'public'
                    )
                );
            } else {
                $factoryController->add($factoryController->getCustomMethod('__construct', '', '', '', 'public'));
            }

            foreach (self::METHODS as $key => $method) {
                $customMethod = '';

                if ('none' != $model) {
                    $modelMethod = Str::of('return ')->concat('$this->')->concat($camelModelClass)->concat('->')->get();

                    $modelMethod .= Str::of($method . $dataModel->class)
                        ->replace('Model', '')
                        ->replace('model', '')
                        ->concat('DB();')
                        ->get();

                    $customMethod = $factoryController->getCustomMethod(
                        Str::of($method . $dataController->class)->replace('Controller', '')->replace('controller', '')->get(),
                        $method === 'read' ? 'array|object' : 'object',
                        in_array($method, ['update', 'delete'], true) ? 'string $id' : '',
                        $modelMethod,
                        'public',
                        $method === 'delete' ? 1 : 2
                    );
                } else {
                    $customMethod = $factoryController->getCustomMethod(
                        Str::of($method . $dataController->class)->replace('Controller', '')->replace('controller', '')->get(),
                        $method === 'read' ? 'array|object' : 'object',
                        in_array($method, ['update', 'delete'], true) ? 'string $id' : '',
                        $method === 'read' ? "return [];" : "return success();",
                        'public',
                        $method === 'delete' ? 1 : 2
                    );
                }

                $factoryController->add($customMethod);
            }

            $factoryController->add("}\n")->close();

            $output->writeln($this->warningOutput("\t>>  CONTROLLER: {$dataController->class}"));

            $output->writeln(
                $this->successOutput("\t>>  CONTROLLER: the '{$dataController->namespace}\\{$dataController->class}' controller has been generated")
            );

            if ('none' != $model) {
                $this->getApplication()->find('new:model')->run(new ArrayInput(['model' => $model]), $output);
            }

            return Command::SUCCESS;
        });
    }
}
