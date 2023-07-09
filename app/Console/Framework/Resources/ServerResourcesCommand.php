<?php

namespace App\Console\Framework\Resources;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerResourcesCommand extends Command {

    use ConsoleOutput;

	protected static $defaultName = "resource:serve";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->write($this->successOutput("\nLion-Framework "));
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this
            ->setDescription("Command required to run resources")
            ->addArgument('resource', InputArgument::REQUIRED, 'Resource name')
            ->addOption('port', "p", InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 7000)
            ->addOption('host', "s", InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', "127.0.0.1");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $resource = $input->getArgument("resource");
        $port = $input->getOption("port");
        $host = $input->getOption("host");

        $url = "{$host}:{$port}";
        $output->writeln($this->warningOutput("\t>>  LOCAL:</comment> Server running on <href=http://{$url}>[http://{$url}]</>"));
        $output->writeln($this->warningOutput("\t>>  HOST:</comment> use --host to expose"));
        $output->writeln($this->warningOutput("\t>>  PORT:</comment> use --port to expose"));
        $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));
        kernel->execute("php -S {$url} -t resources/{$resource}/", false);
        return Command::SUCCESS;
    }

}
