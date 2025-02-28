<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use LogicException;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a capsule class and its defined properties
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CapsuleCommand extends Command
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [Modify and build arrays with different indexes or values]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): CapsuleCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): CapsuleCommand
    {
        $this->str = $str;

        return $this;
    }

    #[Inject]
    public function setArr(Arr $arr): CapsuleCommand
    {
        $this->arr = $arr;

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
            ->setName('new:capsule')
            ->setDescription('Command required for creating new custom capsules')
            ->addArgument('capsule', InputArgument::OPTIONAL, 'Capsule name', 'Example')
            ->addOption(
                'properties',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Defined properties for the capsule',
                []
            )
            ->addOption('entity', 'e', InputOption::VALUE_OPTIONAL, 'Entity name', '');
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
     * @return int
     *
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $capsule */
        $capsule = $input->getArgument('capsule');

        /** @var array<int, string> $properties */
        $properties = $input->getOption('properties');

        /** @var string $entity */
        $entity = $input->getOption('entity');

        $this->classFactory->classFactory('database/Class/', $capsule);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $listProperties = [];

        $listMethods = [];

        /**
         * Iterates the list of data to generate the Getter and Setter methods
         */
        foreach ($properties as $propierty) {
            $split = explode(':', $propierty);

            $data = $this->classFactory->getProperty(
                $split[0],
                $class,
                (!empty($split[1]) ? $split[1] : 'string'),
                ClassFactory::PRIVATE_PROPERTY
            );

            /** @var stdClass $variable */
            $variable = $data->variable;

            /** @var stdClass $type */
            $type = $variable->type;

            /** @var string $snakeCase */
            $snakeCase = $type->snake;

            /** @var stdClass $getter */
            $getter = $data->getter;

            /** @var stdClass $setter */
            $setter = $data->setter;

            /** @var string $getterMethod */
            $getterMethod = $getter->method;

            /** @var string $setterMethod */
            $setterMethod = $setter->method;

            $listProperties[] = $snakeCase;

            $listMethods[] = [
                'getter' => $getterMethod,
                'setter' => $setterMethod,
                'config' => $data,
            ];
        }

        $this->store->folder($folder);

        $this->str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("namespace")->spaces(1)
            ->concat($namespace)
            ->concat(";")->ln()->ln()
            ->concat('use Lion\Bundle\Interface\CapsuleInterface;')->ln()
            ->concat('use Lion\Bundle\Traits\CapsuleTrait;')->ln()->ln()
            ->concat("/**\n * Capsule for the '{$class}' entity")->ln()
            ->concat(' *')->ln()
            ->concat(' * @property string $entity [Entity name]');

        if (count($listMethods) > 0) {
            $this->str->ln();
        }

        /**
         * Iterate through the list of methods to add a class property
         * annotation
         */
        foreach ($listMethods as $method) {
            /** @var stdClass $config */
            $config = $method['config'];

            /** @var stdClass $variable */
            $variable = $config->variable;

            /** @var stdClass $annotations */
            $annotations = $variable->annotations;

            /** @var stdClass $classAnnotations */
            $classAnnotations = $annotations->class;

            /** @var string $dataType */
            $dataType = $classAnnotations->data_type_with_null;

            $this->str->concat(" * {$dataType}\n");
        }

        $this->str
            ->concat(" *\n * @package {$namespace}\n */\n")
            ->concat("class")->spaces()
            ->concat($class)->spaces()
            ->concat('implements CapsuleInterface')->ln()
            ->concat("{")->ln()
            ->lt()->concat('use CapsuleTrait;')->ln()->ln()
            ->lt()->concat(
                <<<PHP
                /**
                     * [Entity name]
                     *
                     * @var string \$entity
                     */
                    private static string \$entity = '{$entity}';

                PHP
            )->ln();

        if (count($properties) > 0) {
            $this->str
                ->lt()->concat($this->arr->of($listProperties)->join("\n\t"))
                ->ln();
        }

        if ($this->arr->of($properties)->length() > 0) {
            $this->str
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                ->lt()->concat("public function capsule(): {$class}")->ln()
                ->lt()->concat('{')->ln()
                ->lt()->lt()->concat('$this')->ln();

            /**
             * Iterate through the list of methods to add the dataset using HTTP
             * requests
             */
            foreach ($listMethods as $key => $method) {
                /** @var stdClass $config */
                $config = $method['config'];

                /** @var stdClass $setter */
                $setter = $config->setter;

                /** @var string $setterMethod */
                $setterMethod = $setter->name;

                /** @var stdClass $format */
                $format = $config->format;

                /** @var string $snakeCase */
                $snakeCase = $format->snake;

                $this->str
                    ->lt()->lt()->lt()->concat('->')
                    ->concat($setterMethod)
                    ->concat("(request('{$snakeCase}'))")
                    ->concat($key === (count($listMethods) - 1) ? ';' : '')->ln();
            }

            $this->str->ln()->lt()->lt()->concat('return $this;')->ln()
                ->lt()->concat('}');
        } else {
            $this->str
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                ->lt()->concat("public function capsule(): {$class}")->ln()
                ->lt()->concat('{')->ln()
                ->lt()->lt()->concat('return $this;')->ln()
                ->lt()->concat('}');
        }

        if (count($properties) > 0) {
            $this->str->ln()->ln();

            /**
             * Iterate through the list of methods to add Getter and Setter
             * methods
             */
            foreach ($listMethods as $key => $method) {
                $this->str->concat($method['getter'])->ln()->ln();

                if ($key === (count($listMethods) - 1)) {
                    $this->str->concat($method['setter'])->ln();
                } else {
                    $this->str->concat($method['setter'])->ln()->ln();
                }
            }
        } else {
            $this->str->ln();
        }

        /** @var string $contentFile */
        $contentFile = $this->str
            ->concat("}")
            ->ln()
            ->get();

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add($contentFile)
            ->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  CAPSULE: the '{$namespace}\\{$class}' capsule has been generated")
        );

        return parent::SUCCESS;
    }
}
