<?php

namespace App\Console\Framework\Resources;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \ZipArchive;

class NewResourcesCommand extends Command {

    use ClassPath, ConsoleOutput;

    protected static $defaultName = "resource:new";

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command required to generate a resource")
            ->addArgument('resource', InputArgument::OPTIONAL, 'Resource name', "example");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $resource = $input->getArgument("resource");

        // unzip template in zip format
        $zip = new ZipArchive();
        $zip->open(storage_path("framework/templates/twig/example.zip", false));
        $zip->extractTo("resources/");
        $zip->close();

        // rename resource folder
        $output->writeln($this->warningOutput("\t>>  RESOURCES: {$resource}"));

        if (is_dir("resources/example/")) {
            if (isSuccess(Store::exist("resources/{$resource}/"))) {
                $output->writeln($this->errorOutput("\t>>  RESOURCES: A resource with this name already exists"));
                return Command::FAILURE;
            } else {
                if (rename("resources/example/", "resources/{$resource}/")) {
                    $output->writeln($this->successOutput("\t>>  RESOURCES: The 'resources/{$resource}/' resource has been generated"));
                    return Command::SUCCESS;
                } else {
                    $output->writeln($this->errorOutput("\t>>  RESOURCES: Failed to generate resource"));
                    return Command::FAILURE;
                }
            }
        }

        return Command::FAILURE;
    }

}
