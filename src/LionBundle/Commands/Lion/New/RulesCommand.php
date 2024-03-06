<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RulesCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): RulesCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): RulesCommand
    {
        $this->store = $store;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('new:rule')
            ->setDescription('Command required for rule creation')
            ->addArgument('rule', InputArgument::OPTIONAL, 'Rule name', 'ExampleRule');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rule = $input->getArgument('rule');

        $this->classFactory->classFactory('app/Rules/', $rule);

        $folder = $this->classFactory->getFolder();
        $class = $this->classFactory->getClass();
        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, 'php', $folder)
            ->add("<?php\n\ndeclare(strict_types=1);\n\n")
            ->add("namespace {$namespace};\n\n")
            ->add("use Lion\Bundle\Helpers\Rules;\n")
            ->add("use Lion\Bundle\Interface\RulesInterface;\n")
            ->add("use Valitron\Validator;\n\n")
            ->add("/**\n * [Rule defined for the '' property]\n *\n * @package {$namespace}\n */\n")
            ->add("class {$class} extends Rules implements RulesInterface\n{\n")
            ->add("\t/**\n\t * [field for '']\n\t *\n\t * @var string $" . 'field' . "\n\t */\n")
            ->add("\t" . 'public string $field = ' . "''" . ';' . "\n\n")
            ->add("\t/**\n\t * [description for '']\n\t *\n\t * @var string $" . 'desc' . "\n\t */\n")
            ->add("\t" . 'public string $desc = ' . "''" . ';' . "\n\n")
            ->add("\t/**\n\t * [value for '']\n\t *\n\t * @var string $" . 'value' . "\n\t */\n")
            ->add("\t" . 'public string $value = ' . "''" . ';' . "\n\n")
            ->add(
                (
                    "\t/**\n\t * [Defines whether the column is optional for postman collections]\n\t *\n" .
                    "\t * @var string $" . 'value' . "\n\t */\n"
                )
            )
            ->add("\t" . 'public bool $disabled = false;' . "\n\n")
            ->add("\t/**\n\t * {@inheritdoc}\n\t * */\n")
            ->add("\tpublic function passes(): void\n\t{\n")
            ->add("\t\t" . '$this->validate(function(Validator $validator) {' . "\n")
            ->add("\t\t\t" . '$validator->rule(' . "'required'" . ', $this->field)->message(')
            ->add("'property is required'" . ');' . "\n\t\t});\n")
            ->add("\t}\n}")
            ->close();

        $output->writeln($this->warningOutput("\t>>  RULE: {$class}"));
        $output->writeln($this->successOutput("\t>>  RULE: the '{$namespace}\\{$class}' rule has been generated"));

        return Command::SUCCESS;
    }
}
