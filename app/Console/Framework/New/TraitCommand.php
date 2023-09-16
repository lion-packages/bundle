<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TraitCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "new:trait";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
        $this
            ->setDescription("Command required for trait creation")
            ->addArgument('trait', InputArgument::OPTIONAL, 'Trait name', "ExampleTrait");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $trait = $input->getArgument('trait');
        $list = $this->export("app/Traits/", $trait);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);

        $this->add(
            str->of('<?php')->ln()->ln()
                ->concat('declare(strict_types=1);')->ln()->ln()
                ->concat('namespace')->spaces(1)
                ->concat("{$list['namespace']};")->ln()->ln()
                ->concat('trait')->spaces(1)
                ->concat($list['class'])->spaces(1)->ln()
                ->concat('{')->ln()->ln()
                ->concat('}')->ln()
                ->get()
        );

        $this->force();
        $this->close();

        $output->writeln($this->warningOutput("\t>>  TRAIT: {$list['class']}"));

        $output->writeln(
            $this->successOutput("\t>>  TRAIT: the '{$list['namespace']}\\{$list['class']}' trait has been generated")
        );

        return Command::SUCCESS;
    }

}
