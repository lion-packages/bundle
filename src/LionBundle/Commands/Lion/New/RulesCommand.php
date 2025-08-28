<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a rule.
 */
class RulesCommand extends Command
{
    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace).
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * Manipulate system files.
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): RulesCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): RulesCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new:rule')
            ->setDescription('Command required for rule creation')
            ->addArgument('rule', InputArgument::OPTIONAL, 'Rule name', 'ExampleRule')
            ->addOption('field', 'f', InputOption::VALUE_OPTIONAL, 'Field', 'example');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes.
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return int
     *
     * @throws Exception If the file could not be opened.
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $rule */
        $rule = $input->getArgument('rule');

        /** @var string $field */
        $field = $input->getOption('field');

        $this->classFactory->classFactory('app/Rules/', $rule);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        if ($this->classFactory->omit(ClassFactory::PHP_EXTENSION)) {
            $output->writeln($this->warningOutput("\t>>  RULE: {$namespace}\\{$class}"));

            $output->writeln($this->infoOutput("\t>>  RULE: This class already exists, the file has been skipped."));

            return parent::SUCCESS;
        }

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Route\Helpers\Rules;
                use Lion\Route\Interface\RulesInterface;
                use Valitron\Validator;

                /**
                 * Rule defined for the '{$field}' property.
                 */
                class {$class} extends Rules implements RulesInterface
                {
                    /**
                     * Field for the '{$field}' property.
                     *
                     * @var string \$field
                     */
                    public string \$field = '{$field}';

                    /**
                     * {@inheritDoc}
                     */
                    public function passes(): void
                    {
                        \$this->validate(function (Validator \$validator): void {
                            \$validator
                                ->rule('required', \$this->field)
                                ->message("The '{\$this->field}' property is required.");
                        });
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  RULE: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  RULE: The rule was generated successfully."));

        return parent::SUCCESS;
    }
}
