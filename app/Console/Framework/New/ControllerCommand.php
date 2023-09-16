<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ArrayInput, InputInterface, InputArgument, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends Command
{
    use ClassPath, ConsoleOutput;

    protected static $defaultName = 'new:controller';

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required for the creation of new Controllers')
            ->addArgument('controller', InputArgument::OPTIONAL, 'Controller name', "ExampleController")
            ->addOption('model', "m", InputOption::VALUE_REQUIRED, 'Do you want to create the model?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $controller = $input->getArgument('controller');
        $model = $input->getOption('model');
        $list = $this->export("app/Http/Controllers/", $controller);
        $list_model = null;
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        $camel_class = "";
        Store::folder($url_folder);

        if ($model != null) {
            $list_model = $this->export("app/models/", $model);
        }

        $this->create($url_folder, $list['class']);
        $this->add("<?php\n\ndeclare(strict_types=1);\n\n");
        $this->add("namespace {$list['namespace']};\n\n");

        if ($model != null) {
            $this->add("use {$list_model['namespace']}\\{$list_model['class']};\n\n");
        }

        $this->add("class {$list['class']}\n{\n");

        if ($model != null) {
            $camel_class = str->of(lcfirst($list_model['class']))->trim()->get();
            $this->add(
                str->of("\tprivate {$list_model['class']} $")
                    ->concat($camel_class)
                    ->concat(";")->ln()->ln()
                    ->get()
            );

            $this->add(
                "\tpublic function __construct()\n\t{\n\t\t" . '$this->' . "{$camel_class} = new {$list_model['class']}();\n\t}\n\n"
            );
        } else {
            $this->add("\tpublic function __construct()\n\t{\n\n\t}\n\n");
        }

        foreach (["create", "read", "update", "delete"] as $key => $method) {
            if ($model != null) {
                $this->add($this->generateFunctionsController(
                    $method,
                    $list['class'],
                    ($method === 'delete' ? true : false),
                    $camel_class
                ));
            } else {
                $this->add($this->generateFunctionsController(
                    $method,
                    $list['class'],
                    ($method === 'delete' ? true : false)
                ));
            }
        }

        $this->add("}\n");
        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  CONTROLLER: {$controller}"));
        $output->writeln(
            $this->successOutput(
                "\t>>  CONTROLLER: the '{$list['namespace']}\\{$list['class']}' controller has been generated"
            )
        );

        if ($model != null) {
            $this->getApplication()->find('new:model')->run(
                new ArrayInput(['model' => $model]),
                $output
            );
        }

        return Command::SUCCESS;
    }
}
