<?php

namespace App\Console\Framework\New;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TraitCommand extends Command {

    use ClassPath;

	protected static $defaultName = "new:trait";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this
            ->setDescription("Command required for trait creation")
            ->addArgument('trait', InputArgument::OPTIONAL, 'Trait name', "ExampleTrait");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $trait = $input->getArgument('trait');
        $list = $this->export("app/Traits/", $trait);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add("<?php\n\n");
        $this->add("namespace {$list['namespace']};\n\n");
        $this->add("trait {$list['class']} {\n\n}");
        $this->force();
        $this->close();

        $output->writeln("<comment>\t>>  TRAIT: {$trait}</comment>");
        $output->writeln("<info>\t>>  TRAIT: The '{$list['namespace']}\\{$list['class']}' trait has been generated</info>");

        return Command::SUCCESS;
    }

}
