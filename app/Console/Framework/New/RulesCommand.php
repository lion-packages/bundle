<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RulesCommand extends Command
{
    use ClassPath, ConsoleOutput;

    protected static $defaultName = "new:rule";

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required for rule creation')
            ->addArgument('rule', InputArgument::OPTIONAL, 'Rule name', 'ExampleRule');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rule = $input->getArgument('rule');
        $list = $this->export("app/Rules/", $rule);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add("<?php\n\ndeclare(strict_types=1);\n\n");
        $this->add("namespace {$list['namespace']};\n\n");
        $this->add("use App\Traits\Framework\ShowErrors;\n");
        $this->add("use Valitron\Validator;\n\n");
        $this->add("class {$list['class']} \n{\n");
        $this->add("\tuse ShowErrors;\n\n");
        $this->add("\t" . 'public static string $field = "";' . "\n");
        $this->add("\t" . 'public static string $desc = "";' . "\n");
        $this->add("\t" . 'public static string $value = "";' . "\n");
        $this->add("\t" . 'public static bool $disabled = false;' . "\n\n");
        $this->add("\tpublic static function passes(): void \n\t{\n");
        $this->add("\t\t" . 'self::validate(function(Validator $validator) {' . "\n");
        $this->add("\t\t\t" . '$validator->rule("required", self::$field)->message("property is required");' . "\n\t\t});\n");
        $this->add("\t}\n}");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  RULE: {$list['class']}"));

        $output->writeln(
            $this->successOutput("\t>>  RULE: the '{$list['namespace']}\\{$list['class']}' rule has been generated")
        );

        return Command::SUCCESS;
    }
}
