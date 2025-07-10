<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a Controller class
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class ControllerCommand extends Command
{
    /**
     * List of methods generated in the class
     *
     * @const METHODS
     */
    private const array METHODS = [
        'create',
        'read',
        'update',
        'delete',
    ];

    /**
     * Controller path
     *
     * @const PATH_CONTROLLER
     */
    public const string PATH_CONTROLLER = 'app/Http/Controllers/';

    /**
     * Model path
     *
     * @const PATH_MODEL
     */
    public const string PATH_MODEL = 'app/Models/';

    /**
     * Modify and construct strings with different formats
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * Allows adding several ClassFactory type objects for multiple management
     *
     * @var ClassCommandFactory $classCommandFactory
     */
    private ClassCommandFactory $classCommandFactory;

    #[Inject]
    public function setStr(Str $str): ControllerCommand
    {
        $this->str = $str;

        return $this;
    }

    #[Inject]
    public function setClassCommandFactory(ClassCommandFactory $classCommandFactory): ControllerCommand
    {
        $this->classCommandFactory = $classCommandFactory;

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
            ->setName('new:controller')
            ->setDescription('Command required for the creation of new Controllers')
            ->addArgument('controller', InputArgument::OPTIONAL, 'Controller name', 'ExampleController')
            ->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to create the model?', 'none');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return int
     *
     * @throws Exception If the file could not be opened
     * @throws ExceptionInterface When input binding fails. Bypass this by calling
     * ignoreValidationErrors()
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->classCommandFactory
            ->setFactories(['controller', 'model'])
            ->execute(function (ClassCommandFactory $classFactory, Store $store) use ($input, $output): int {
                $str = new Str();

                /** @var string $controller */
                $controller = $input->getArgument('controller');

                /** @var string|null $model */
                $model = $input->getOption('model');

                if (null === $model) {
                    /** @var string $model */
                    $model = $str
                        ->of($model)
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
                    'class' => $controller,
                ]);

                $dataModel = $classFactory->getData($factoryModel, [
                    'path' => self::PATH_MODEL,
                    'class' => $model,
                ]);

                /** @var string $controllerClass */
                $controllerClass = $dataController->class;

                /** @var string $controllerPath */
                $controllerPath = $dataController->folder;

                /** @var string $controllerNamespace */
                $controllerNamespace = $dataController->namespace;

                /** @var string $modelClass */
                $modelClass = $dataModel->class;

                /** @var string $modelNamespace */
                $modelNamespace = $dataModel->namespace;

                $camelModelClass = lcfirst($modelClass);

                $store->folder($controllerPath);

                $this->str
                    ->of(
                        <<<PHP
                        <?php

                        declare(strict_types=1);

                        namespace {$controllerNamespace};

                        PHP
                    );

                if ('none' != $model) {
                    $this->str->concat("\nuse {$modelNamespace}\\{$modelClass};");
                }

                $this->str
                    ->concat(
                        <<<PHP

                        use Lion\Database\Interface\DatabaseCapsuleInterface;
                        use stdClass;

                        /**
                         * Description of Controller '{$controllerClass}'
                         */
                        class {$controllerClass}
                        {\n
                        PHP
                    );

                foreach (self::METHODS as $method) {
                    /** @var string $controllerMethod */
                    $controllerMethod = $str
                        ->of($method . $controllerClass)
                        ->replace('Controller', '')
                        ->replace('controller', '')
                        ->get();

                    $methodType = $method === 'read'
                        ? [
                            'type' => 'array|stdClass',
                            // phpcs:ignore Generic.Files.LineLength.TooLong
                            'annotation' => 'array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass',
                        ]
                        : 'int|stdClass';

                    if ('none' != $model) {
                        $methodParams = in_array($method, ['update', 'delete'], true)
                            ? ($modelClass . ' $' . $camelModelClass . ', string $id')
                            : ($modelClass . ' $' . $camelModelClass);

                        /** @var string $modelMethod */
                        $modelMethod = $str
                            ->of('return ')
                            ->concat('$')
                            ->concat($camelModelClass)
                            ->concat('->')
                            ->get();

                        $modelMethod .= $str
                            ->of($method . $modelClass)
                            ->replace('Model', '')
                            ->replace('model', '')
                            ->concat('DB();')
                            ->get();

                        $customMethod = $factoryController->getCustomMethod(
                            $controllerMethod,
                            $methodType,
                            $methodParams,
                            $modelMethod,
                            'public',
                            $method === 'delete' ? 1 : 2
                        );
                    } else {
                        $customMethod = $factoryController->getCustomMethod(
                            $controllerMethod,
                            $methodType,
                            (in_array($method, ['update', 'delete'], true) ? 'string $id' : ''),
                            $method === 'read' ? "return [];" : "return success();",
                            'public',
                            $method === 'delete' ? 1 : 2
                        );
                    }

                    $this->str->concat($customMethod);
                }

                /** @var string $content */
                $content = $this->str
                    ->concat(
                        <<<PHP
                        }

                        PHP
                    )
                    ->get();

                $factoryController
                    ->create($controllerClass, ClassFactory::PHP_EXTENSION, $controllerPath)
                    ->add($content);

                $output->writeln($this->warningOutput("\t>>  CONTROLLER: {$controllerNamespace}\\{$controllerClass}"));

                $output->writeln(
                    $this->successOutput("\t>>  CONTROLLER: The controller class has been generated successfully.")
                );

                if ('none' != $model) {
                    $arrayInput = [
                        'model' => $model,
                    ];

                    /** @phpstan-ignore-next-line */
                    $this
                        ->getApplication()
                        ->find('new:model')
                        ->run(new ArrayInput($arrayInput), $output);
                }

                return parent::SUCCESS;
            });
    }
}
