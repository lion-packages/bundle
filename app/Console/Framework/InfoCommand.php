<?php

namespace App\Console\Framework;

use LionHelpers\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command {

	protected static $defaultName = "info";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription("Command to display basic project information and libraries");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $composer_json = json->decode(file_get_contents("composer.json"));
        $homepage = "https://lion-client.vercel.app";
        $libraries = [];

        $output->writeln("\n\t<question> INFO </question> APP NAME: " . env->APP_NAME . "\n");
        $output->writeln("\t<question> INFO </question> LICENSE: {$composer_json['license']}\n");
        $output->writeln("\t<question> INFO </question> HOMEPAGE: <href={$homepage}>[{$homepage}]</>\n");

        foreach ($composer_json['require'] as $key => $library) {
            if ($key != "php") {
                $exec_response = [];
                exec("composer show {$key} --direct --format=json", $exec_response);
                $json = json->decode(Arr::of($exec_response)->join(" "));

                $libraries[] = [
                    $key,
                    "<fg=#FFB63E>{$json['versions'][0]}</>",
                    "<fg=#FFB63E>{$json['licenses'][0]['osi']}</>",
                    "<fg=#E37820>false</>",
                    $json['description']
                ];
            }
        }

        foreach ($composer_json['require-dev'] as $key => $library) {
            if ($key != "php") {
                $exec_response = [];
                exec("composer show {$key} --direct --format=json", $exec_response);
                $json = json->decode(Arr::of($exec_response)->join(" "));

                $libraries[] = [
                    $key,
                    "<fg=#FFB63E>{$json['versions'][0]}</>",
                    "<fg=#FFB63E>{$json['licenses'][0]['osi']}</>",
                    "<fg=#E37820>true</>",
                    $json['description']
                ];
            }
        }

        $size = Arr::of($libraries)->length();

        (new Table($output))
            ->setHeaderTitle('<info> LIBRARIES </info>')
            ->setHeaders(['LIBRARY', 'VERSION', 'LICENSE', 'DEV', 'DESCRIPTION'])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [" . $size . "] libraries </info>"
                    : ($size === 1
                        ? "<info> showing a single library </info>"
                        : "<info> No libraries available </info>"
                    )
            )
            ->setRows($libraries)
            ->render();

		return Command::SUCCESS;
	}

}
