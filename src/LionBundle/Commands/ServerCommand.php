<?php

declare(strict_types=1);

namespace LionBundle\Commands;

use LionCommand\Command;
use LionCommand\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    private string|float $start;
	protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->start = microtime(true);
	}

	protected function configure(): void
    {
		$this
            ->setName('serve')
            ->setDescription('Created command to start server locally')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 8000)
            ->addOption('host', 's', InputOption::VALUE_OPTIONAL, 'Do you want to set your own host?', '127.0.0.1');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$port = $input->getOption('port');
        $host = $input->getOption('host');

        $url = "{$host}:{$port}";
        $link = "<href=http://{$url}>[http://{$url}]</>";

        $output->write("\033[2J\033[;H");
        $output->write($this->successOutput("\nLion-Framework "));
        $output->writeln("ready in " . number_format((microtime(true) - $this->start), 3) . " ms\n");
        $output->writeln($this->warningOutput("\t>>  LOCAL:</comment> Server running on {$link}"));
        $output->writeln($this->warningOutput("\t>>  HOST:</comment> use --host to expose"));
        $output->writeln($this->warningOutput("\t>>  PORT:</comment> use --port to expose"));
        $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));

        (new Kernel())->execute("php -S {$host}:{$port} -t public", false);

        return Command::SUCCESS;
	}
}
