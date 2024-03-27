<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a Socket class
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class SocketCommand extends Command
{
    /**
     * [ClassFactory class object]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * @required
     * */
    public function setClassFactory(ClassFactory $classFactory): SocketCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    /**
     * @required
     * */
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
        $this->classFactory->classFactory('app/Http/Sockets/', $input->getArgument('socket'));

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Exception;
                use Ratchet\ConnectionInterface;
                use Ratchet\MessageComponentInterface;
                use SplObjectStorage;

                /**
                 * Description of Socket '{$class}'
                 *
                 * @property SplObjectStorage \$clients [List of clients connected to the Socket]
                 *
                 * @package {$namespace}
                 */
                class {$class} implements MessageComponentInterface
                {
                    /**
                     * [Defines the Socket Port]
                     *
                     * @const PORT
                     */
                    const PORT = 9000;

                    /**
                     * [Defines the Socket Host]
                     *
                     * @const HOST
                     */
                    const HOST = '0.0.0.0';

                    /**
                     * [List of clients connected to the Socket]
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
                     * {@inheritdoc}
                     */
                    public function onOpen(ConnectionInterface \$conn): void
                    {
                        echo("New connection! ({\$conn->resourceId})");

                        \$this->clients->attach(\$conn);
                    }

                    /**
                     * {@inheritdoc}
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
                     * {@inheritdoc}
                     */
                    public function onClose(ConnectionInterface \$conn): void
                    {
                        \$this->clients->detach(\$conn);
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function onError(ConnectionInterface \$conn, Exception \$e): void
                    {
                        \$conn->close();
                    }
                }

                EOT
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SOCKET: {$class}"));

        $output->writeln($this->successOutput("\t>>  SOCKET: the '{$namespace}\\{$class}' socket has been generated"));

        return Command::SUCCESS;
	}
}
