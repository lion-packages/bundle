<?php

declare(strict_types=1);

namespace LionBundle\Commands\New;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RulesCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->classFactory = new ClassFactory();
        $this->store = new Store();
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
            ->add("use App\Traits\Framework\ShowErrors;\n")
            ->add("use Valitron\Validator;\n\n")
            ->add("class {$class} \n{\n")
            ->add("\tuse ShowErrors;\n\n")
            ->add("\t" . 'public static string $field = "";' . "\n")
            ->add("\t" . 'public static string $desc = "";' . "\n")
            ->add("\t" . 'public static string $value = "";' . "\n")
            ->add("\t" . 'public static bool $disabled = false;' . "\n\n")
            ->add("\tpublic static function passes(): void \n\t{\n")
            ->add("\t\t" . 'self::validate(function(Validator $validator) {' . "\n")
            ->add("\t\t\t" . '$validator->rule("required", self::$field)->message("property is required");' . "\n\t\t});\n")
            ->add("\t}\n}")
            ->close();

        $output->writeln($this->warningOutput("\t>>  RULE: {$class}"));
        $output->writeln($this->successOutput("\t>>  RULE: the '{$namespace}\\{$class}' rule has been generated"));

        return Command::SUCCESS;
    }
}
