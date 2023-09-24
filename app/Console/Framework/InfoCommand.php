<?php

namespace App\Console\Framework;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "info";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this->setDescription("Command to display basic project information and libraries");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer_json = json->decode(file_get_contents("composer.json"));
        $homepage = "https://lion-client.vercel.app";
        $libraries = [];

        $output->write($this->infoOutput("\n\t INFO ") . " APP NAME: " . env->APP_NAME . "\n");
        $output->write($this->infoOutput("\n\t INFO ") . " LICENSE: {$composer_json['license']}\n");
        $output->write($this->infoOutput("\n\t INFO ") . " HOMEPAGE: <href={$homepage}>[{$homepage}]</>\n\n");

        foreach ($composer_json['require'] as $key => $library) {
            if ($key != "php") {
                $exec_response = [];
                exec("composer show {$key} --direct --format=json", $exec_response);
                $json = json->decode(arr->of($exec_response)->join(" "));

                $libraries[] = [
                    $key,
                    $this->warningOutput($json['versions'][0]),
                    $this->warningOutput($json['licenses'][0]['osi']),
                    $this->errorOutput("false"),
                    $json['description']
                ];
            }
        }

        foreach ($composer_json['require-dev'] as $key => $library) {
            if ($key != "php") {
                $exec_response = [];
                exec("composer show {$key} --direct --format=json", $exec_response);
                $json = json->decode(arr->of($exec_response)->join(" "));

                $libraries[] = [
                    $key,
                    $this->warningOutput($json['versions'][0]),
                    $this->warningOutput($json['licenses'][0]['osi']),
                    $this->errorOutput("true"),
                    $json['description']
                ];
            }
        }

        $size = arr->of($libraries)->length();

        (new Table($output))
            ->setHeaderTitle($this->successOutput(' LIBRARIES '))
            ->setHeaders(['LIBRARY', 'VERSION', 'LICENSE', 'DEV', 'DESCRIPTION'])
            ->setFooterTitle(
                $size > 1
                    ? $this->successOutput(" Showing [" . $size . "] libraries ")
                    : ($size === 1
                        ? $this->successOutput(" showing a single library ")
                        : $this->successOutput(" No libraries available ")
                    )
            )
            ->setRows($libraries)
            ->render();

		return Command::SUCCESS;
	}
}
