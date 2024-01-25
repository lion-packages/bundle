<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Sockets;

use Lion\Command\Command;
use Lion\DependencyInjection\Container;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ServerSocketCommand extends Command
{
    private Container $container;

    /**
     * @required
     * */
    public function setContainer(Container $container): ServerSocketCommand
    {
        $this->container = $container;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('socket:serve')
            ->setDescription('Command required to run WebSockets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classList = [];

        foreach ($this->container->getFiles('./app/Http/Sockets/') as $file) {
            $classList[] = $this->container->getNamespace($file, 'App\\Http\\Sockets\\', 'Sockets/');
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $selectedSocket = $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select a socket ' . $this->warningOutput('(default: ' . reset($classList) . ')')),
                $classList,
                0
            )
        );

        $socketClass = new $selectedSocket();
        $url = 'ws://' . $socketClass::HOST . ':' . $socketClass::PORT;

        $output->write("\033[2J\033[;H");
        $output->write($this->successOutput("\nLion-Framework "));
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
        $output->writeln($this->warningOutput("\t>>  LOCAL: Socket running on [{$url}]"));
        $output->writeln($this->warningOutput("\t>>  Press Ctrl+C to stop the socket"));

        IoServer::factory(
                new HttpServer(new WsServer($socketClass)),
                $socketClass::PORT,
                $socketClass::HOST
            )
            ->run();

        return Command::SUCCESS;
    }
}
