<?php

namespace App\Console\Framework\Resources;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerResourcesCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "resource:serve";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
        $this
            ->setDescription("Command required to run resources")
            ->addArgument('resource', InputArgument::REQUIRED, 'Resource name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resource = $input->getArgument("resource");
        $resources = kernel->getResources();
        $all_resources = [...$resources['framework'], ...$resources['app']];
        $rsc = $all_resources[$resource];

        if ($rsc['type'] === "twig") {
            $output->write($this->successOutput("\nLion-Framework "));
            $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");

            $url = "{$rsc['host']}:{$rsc['port']}";
            $output->writeln($this->warningOutput("\t>>  LOCAL: Server running on <href=http://{$url}>[http://{$url}]</>"));
            $output->writeln($this->warningOutput("\t>>  HOST: use --host to expose"));
            $output->writeln($this->warningOutput("\t>>  PORT: use --port to expose"));
            $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));
            kernel->execute("php -S {$url} -t resources/{$rsc['path']}", false);
        } else {
            $output->writeln($this->errorOutput("\t>>  RESOURCE: The requested resource does not exist"));
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }
}
