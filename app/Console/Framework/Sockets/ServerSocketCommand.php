<?php

namespace App\Console\Framework\Sockets;

use App\Traits\Framework\ConsoleOutput;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerSocketCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "socket:serve";

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription('Command required to run WebSockets')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $socket = $input->getArgument('socket');
        $class = kernel->getClass(str->of($socket)->trim()->get());
        $output->writeln($this->warningOutput("\t>>  SOCKET: {$socket}"));

        if (!$class) {
            $output->writeln($this->errorOutput("\t>>  SOCKET: Socket does not exist"));
            return Command::FAILURE;
        }

        if (!class_exists($class)) {
            $output->writeln($this->errorOutput("\t>>  SOCKET: Socket does not exist"));
            return Command::FAILURE;
        }

        $socket_class = new $class();
        $socket_info = $socket_class->getSocket();

        $output->write("\033[2J\033[;H");
        $output->write($this->successOutput("\nLion-Framework "));
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");

        $url = "ws://{$socket_info->host}:{$socket_info->port}";
        $output->writeln($this->warningOutput("\t>>  LOCAL:</comment> Socket running on [{$url}]"));
        $output->writeln($this->warningOutput("\t>>  Press Ctrl+C to stop the socket"));
        IoServer::factory(new HttpServer(new WsServer($socket_class)), $socket_info->port, $socket_info->host)->run();
        return Command::SUCCESS;
    }
}
