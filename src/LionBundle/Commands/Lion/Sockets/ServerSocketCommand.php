<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Sockets;

use Lion\Command\Command;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ServerSocketCommand extends Command
{
    private Container $container;
    private Store $store;

    /**
     * @required
     * */
    public function setContainer(Container $container): ServerSocketCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): ServerSocketCommand
    {
        $this->store = $store;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('socket:serve')
            ->setDescription('Command required to run WebSockets')
            ->addOption('socket', 's', InputOption::VALUE_OPTIONAL, 'Socket class namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./app/Http/Sockets/'))) {
            $output->writeln($this->errorOutput("\t>> SOCKET: no sockets defined"));

            return Command::FAILURE;
        }

        $socketDefault = $input->getOption('socket');
        $selectedSocket = null;

        if (null === $socketDefault) {
            $selectedSocket = $this->selectSocket($input, $output);

            if ('none' === $selectedSocket) {
                return Command::SUCCESS;
            }
        } else {
            $output->writeln($this->warningOutput("\n(default: {$socketDefault})\n"));

            $selectedSocket = $socketDefault;
        }

        $socketClass = new $selectedSocket();
        $url = 'ws://' . $socketClass::HOST . ':' . $socketClass::PORT;

        $output->write($this->successOutput("\nLion-Framework "));
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

    private function selectSocket(InputInterface $input, OutputInterface $output): string
    {
        $classList = [];

        foreach ($this->container->getFiles('./app/Http/Sockets/') as $file) {
            $classList[] = $this->container->getNamespace($file, 'App\\Http\\Sockets\\', 'Sockets/');
        }

        if (count($classList) === 0) {
            $output->writeln($this->warningOutput("\nNo sockets available\n"));

            return 'none';
        }

        if (count($classList) === 1) {
            $first = reset($classList);
            $output->writeln($this->warningOutput("\n(default: {$first})\n"));

            return $first;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select a socket ' . $this->warningOutput('(default: ' . reset($classList) . ')')),
                $classList,
                0
            )
        );
    }
}
