<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FactoryCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "db:factory";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required for the creation of new factories')
            ->addArgument('factory', InputArgument::OPTIONAL, 'Factory name', "ExampleFactory");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = $input->getArgument('factory');
        $list = $this->export("Database/Factories/", $factory);
        $url_folder = lcfirst(str->of($list['namespace'])->replace("\\", "/")->get());
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add(str->of("<?php")->ln()->ln()->concat('declare(strict_types=1);')->ln()->ln()->get());
        $this->add(str->of("namespace ")->concat($list['namespace'])->concat(";")->ln()->ln()->get());
        $this->add(str->of("use App\Traits\Framework\Faker;")->ln()->ln()->get());
        $this->add(str->of("class ")->concat($list['class'])->concat("\n{")->ln()->get());
        $this->add("\tuse Faker;\n\n");
        $this->add("\t/**\n");
        $this->add("\t * ------------------------------------------------------------------------------\n");
        $this->add("\t * Define the model's default state\n");
        $this->add("\t * ------------------------------------------------------------------------------\n");
        $this->add("\t **/\n");
        $this->add("\tpublic static function definition(): array\n\t{\n\t\treturn [self::get()->name()];\n\t}\n");
        $this->add("}\n");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  FACTORY: {$factory}"));

        $output->writeln(
            $this->successOutput("\t>>  FACTORY: the '{$list['namespace']}\\{$list['class']}' factory has been generated")
        );

        return Command::SUCCESS;
    }
}
