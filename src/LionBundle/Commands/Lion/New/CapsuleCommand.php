<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a capsule class and its defined properties
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 * @property Arr $arr [Arr class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class CapsuleCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): CapsuleCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): CapsuleCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): CapsuleCommand
    {
        $this->str = $str;

        return $this;
    }

    /**
     * @required
     * */
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
     * @return int [0 if everything went fine, or an exit code]
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $capsule = $input->getArgument('capsule');

        $properties = $input->getOption('properties');

        $entity = $input->getOption('entity');

        $this->classFactory->classFactory('database/Class/', $capsule);

        $folder = $this->classFactory->getFolder();

        $namespace = $this->classFactory->getNamespace();

        $class = $this->classFactory->getClass();

        $listProperties = [];

        $listMethods = [];

        foreach ($properties as $key => $propierty) {
            $split = explode(':', $propierty);

            $data = $this->classFactory->getProperty(
                $split[0],
                $class,
                (!empty($split[1]) ? $split[1] : 'string'),
                ClassFactory::PRIVATE_PROPERTY
            );

            $listProperties[] = $data->variable->type->snake;

            $listMethods[] = [
                'getter' => $data->getter->method,
                'setter' => $data->setter->method,
                'config' => $data
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

        foreach ($listMethods as $key => $method) {
            $this->str->concat(" * {$method['config']->variable->annotations->class->data_type_with_null}\n");
        }

        $this->str
            ->concat(" *\n * @package {$namespace}\n */\n")
            ->concat("class")->spaces(1)
            ->concat($class)->spaces(1)
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
                    private string \$entity = '{$entity}';

                PHP
            )->ln();

        if (count($properties) > 0) {
            $this->str->lt()->concat($this->arr->of($listProperties)->join("\n\t"))->ln();
        }

        if ($this->arr->of($properties)->length() > 0) {
            $this->str
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                ->lt()->concat("public function capsule(): {$class}")->ln()
                ->lt()->concat('{')->ln()
                ->lt()->lt()->concat('$this')->ln();

            foreach ($listMethods as $key => $method) {
                $this->str
                    ->lt()->lt()->lt()->concat('->')
                    ->concat($method['config']->setter->name)
                    ->concat("(request('{$method['config']->format->snake}'))")
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

            foreach ($listMethods as $key => $method) {
                if ($key === (count($listMethods) - 1)) {
                    $this->str->concat($method['getter'])->ln()->ln();

                    $this->str->concat($method['setter'])->ln();
                } else {
                    $this->str->concat($method['getter'])->ln()->ln();

                    $this->str->concat($method['setter'])->ln()->ln();
                }
            }
        } else {
            $this->str->ln();
        }

        $contentFile = $this->str->concat("}")->ln()->get();

        $this->classFactory->create($class, ClassFactory::PHP_EXTENSION, $folder)->add($contentFile)->close();

        $output->writeln($this->warningOutput("\t>>  CAPSULE: {$class}"));

        $output->writeln(
            $this->successOutput("\t>>  CAPSULE: the '{$namespace}\\{$class}' capsule has been generated")
        );

        return Command::SUCCESS;
    }
}
