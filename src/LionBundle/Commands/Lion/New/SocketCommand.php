<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a Socket class
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class SocketCommand extends Command
{
    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace)
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * Manipulate system files
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): SocketCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): SocketCommand
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
            ->setName('new:socket')
            ->setDescription('Command required for creating new WebSockets')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket name', 'ExampleSocket');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return int
     *
     * @throws Exception If the file could not be opened
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $socket */
        $socket = $input->getArgument('socket');

        $this->classFactory->classFactory('app/Sockets/', $socket);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Exception;
                use Lion\Bundle\Interface\SocketInterface;
                use Ratchet\ConnectionInterface;
                use SplObjectStorage;

                /**
                 * Sockets are communication channels that allow independent processes to
                 * exchange data locally and across networks. A socket is the endpoint of a
                 * bidirectional communication channel
                 */
                class {$class} implements SocketInterface
                {
                    /**
                     * List of clients connected to the Socket
                     *
                     * @var SplObjectStorage \$clients
                     */
                    protected SplObjectStorage \$clients;

                    /**
                     * Class Constructor
                     */
                    public function __construct()
                    {
                        \$this->clients = new SplObjectStorage();
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public function onOpen(ConnectionInterface \$conn): void
                    {
                        echo("New connection! ({\$conn->resourceId})");

                        \$this->clients->attach(\$conn);
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public function onMessage(ConnectionInterface \$from, \$msg): void
                    {
                        foreach (\$this->clients as \$client) {
                            if (\$from !== \$client) {
                                \$client->send(\$msg);
                            }
                        }
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public function onClose(ConnectionInterface \$conn): void
                    {
                        \$this->clients->detach(\$conn);
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public function onError(ConnectionInterface \$conn, Exception \$e): void
                    {
                        \$conn->close();
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SOCKET: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  SOCKET: The socket has been generated successfully."));

        return parent::SUCCESS;
    }
}
