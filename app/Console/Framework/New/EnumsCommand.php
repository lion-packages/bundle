<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionHelpers\Str;

class EnumsCommand extends Command {

	protected static $defaultName = "new:enum";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating Enum...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
            "Command required for creating new Enums"
        )->addArgument(
            'enum', InputArgument::REQUIRED, 'Enum name', null
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Enums/", $input->getArgument('enum'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add(Str::of("<?php")->ln()->ln()->get());
        ClassPath::add(Str::of("namespace ")->concat($list['namespace'])->concat(";")->ln()->ln()->get());

        ClassPath::add(
            Str::of("enum ")
                ->concat($list['class'])
                ->concat(": string {")->ln()->ln()->lt()
                ->concat("case EXAMPLE = 'example';")->ln()->ln()->lt()
                ->concat("public static function values(): array {")->ln()->lt()->lt()
                ->concat('return array_map(fn($value) => $value->value, self::cases());')->ln()->lt()
                ->concat("}")->ln()->ln()
                ->concat("}")
                ->get()
        );

        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>Capsule created successfully</info>");
        return Command::SUCCESS;
	}

}
