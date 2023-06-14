<?php

namespace App\Console\Framework\Sockets;

use App\Console\Kernel;
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

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription('Command required to run WebSockets')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket name')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Do you want to set your own port?', 8080);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $port = $input->getOption('port');
        $socket = $input->getArgument('socket');
        $class = Kernel::getInstance()->getClass(str->of($socket)->trim()->get());

        if (!$class) {
            $output->writeln("<comment>\t>>  SOCKET: {$socket}</comment>");
            $output->writeln("<fg=#E37820>\t>>  SOCKET: Socket does not exist</>");
            return Command::FAILURE;
        }

        if (!class_exists($class)) {
            $output->writeln("<comment>\t>>  SOCKET: {$socket}</comment>");
            $output->writeln("<fg=#E37820>\t>>  SOCKET: Socket does not exist</>");
            return Command::FAILURE;
        }

        $output->write("\n<info>Lion-Framework</info> ");
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
        $output->writeln("\t<question> INFO </question> WebSocket running on port {$port}\n");
        $output->writeln("<comment>Press Ctrl+C to stop the WebSocket</comment>\n");

        $server = IoServer::factory(new HttpServer(new WsServer(new $class())), $port);
        $server->run();

        return Command::SUCCESS;
    }

}
