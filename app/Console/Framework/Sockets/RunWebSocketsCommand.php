<?php

namespace App\Console\Framework\Sockets;

use App\Console\Kernel;
use LionHelpers\Str;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunWebSocketsCommand extends Command {

	protected static $defaultName = "socket:serve";
    private ?int $port;

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->port = $input->getOption('port');

        if ($this->port === null) {
            $this->port = 8080;
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            'Command required to run WebSockets'
        )->addArgument(
            'web-socket', InputArgument::REQUIRED, 'Socket name', null
        )->addOption(
            'port', null, InputOption::VALUE_REQUIRED, 'Do you want to set your own port?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $class = Kernel::getInstance()->getClass(
            Str::of($input->getArgument('web-socket'))->trim()->get()
        );

        if (!$class) {
            $output->writeln('<error>socket does not exist</error>');
            return Command::FAILURE;
        }

        if (!class_exists($class)) {
            $output->writeln('<error>socket does not exist</error>');
            return Command::FAILURE;
        }

        $output->write("\n<info>Lion-Framework</info> ");
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
        $output->writeln("\t<question> INFO </question> WebSocket running on port {$this->port}\n");
        $output->writeln("<comment>Press Ctrl+C to stop the WebSocket</comment>\n");

        $server = IoServer::factory(new HttpServer(new WsServer(new $class())), $this->port);
        $server->run();

        return Command::SUCCESS;
    }

}
