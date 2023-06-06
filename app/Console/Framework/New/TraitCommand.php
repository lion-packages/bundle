<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionFiles\Store;
use App\Traits\Framework\ClassPath;

class TraitCommand extends Command {

	protected static $defaultName = "new:trait";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this->setDescription(
            "Command required for trait creation"
        )->addArgument(
            'trait', InputArgument::REQUIRED, 'Trait name', null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $list = ClassPath::export("app/Traits/", $input->getArgument('trait'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\n\n");
        ClassPath::add("namespace {$list['namespace']};\n\n");
        ClassPath::add("trait {$list['class']} {\n\n}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>The '{$list['namespace']}\\{$list['class']}' trait has been generated</info>");
        return Command::SUCCESS;
    }

}
