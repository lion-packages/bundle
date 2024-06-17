<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Sockets;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\SocketInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Initialize a socket
 *
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\Sockets
 */
class ServerSocketCommand extends Command
{
    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
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

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('socket:serve')
            ->setDescription('Command required to run WebSockets')
            ->addOption('socket', 's', InputOption::VALUE_OPTIONAL, 'Socket class namespace')
            ->addOption('host', 'o', InputOption::VALUE_OPTIONAL, 'Socket host', '127.0.0.1')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Socket port', 8080);
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./app/Sockets/'))) {
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
            $output->writeln($this->warningOutput("(default: {$socketDefault})"));

            $selectedSocket = $socketDefault;
        }

        /** @var SocketInterface $socketInterface */
        $socketInterface = new $selectedSocket();

        $host = $input->getOption('host');

        $port = $input->getOption('port');

        $url = "ws://{$host}:{$port}";

        $output->writeln($this->successOutput("Lion-Framework\n"));

        $output->writeln($this->warningOutput("\t>>  LOCAL: Socket running on [{$url}]"));

        $output->writeln($this->warningOutput("\t>>  Press Ctrl+C to stop the socket"));

        IoServer::factory(new HttpServer(new WsServer($socketInterface)), $port, $host)
            ->run();

        return Command::SUCCESS;
    }

    /**
     * Open a selection list to select a socket
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     */
    private function selectSocket(InputInterface $input, OutputInterface $output): string
    {
        $classList = [];

        foreach ($this->container->getFiles('./app/Sockets/') as $file) {
            if (isSuccess($this->store->validate([$file], [ClassFactory::PHP_EXTENSION]))) {
                $classList[] = $this->container->getNamespace($file, 'App\\Sockets\\', 'Sockets/');
            }
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
