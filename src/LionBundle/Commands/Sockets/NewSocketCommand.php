<?php

declare(strict_types=1);

namespace LionBundle\Commands\Sockets;

use LionBundle\Helpers\Commands\ClassFactory;
use LionCommand\Command;
use LionFiles\Store;
use LionHelpers\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewSocketCommand extends Command
{
    private ClassFactory $classFactory;
    private Store $store;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->classFactory = new ClassFactory();
        $this->store = new Store();
    }

	protected function configure(): void
    {
        $this
            ->setName('socket:new')
            ->setDescription('Command required for creating new WebSockets')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket name', 'ExampleSocket');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $socket = $input->getArgument('socket');
        $listFactory = $this->classFactory->classFactory('app/Http/Sockets/', $socket);
        $urlFolder = lcfirst(str_replace("\\", "/", $listFactory['namespace']));
        $this->store->folder($urlFolder);

        $this->classFactory
            ->create($listFactory['class'], 'php', "{$urlFolder}/")
            ->add(
                Str::of('<?php')->ln()->ln()
                    ->concat('declare(strict_types=1);')->ln()->ln()
                    ->concat('namespace')->spaces(1)->concat($listFactory['namespace'])->concat(';')->ln()->ln()
                    ->concat('use Exception;')->ln()
                    ->concat('use Ratchet\\ConnectionInterface;')->ln()
                    ->concat('use Ratchet\\MessageComponentInterface;')->ln()
                    ->concat('use SplObjectStorage;')->ln()->ln()
                    ->concat('class')->spaces(1)->concat($listFactory['class'])->spaces(1)->concat('implements')
                    ->spaces()->concat('MessageComponentInterface')->ln()->concat('{')->ln()
                    ->lt()->concat('protected SplObjectStorage $clients;')->ln()
                    ->lt()->concat('protected int $port = 9000;')->ln()
                    ->lt()->concat('protected string $host =')->concat("'0.0.0.0';")->ln()->ln()
                    ->lt()->concat('public function __construct()')->ln()->lt()->concat('{')->ln()
                    ->lt()->lt()->concat('$this->clients = new SplObjectStorage();')->ln()
                    ->lt()->concat('}')->ln()->ln()
                    ->lt()->concat('public function getSocket(): object')->ln()->lt()->concat('{')->ln()
                    ->lt()->lt()->concat('return (object) ["port" => $this->port, "host" => $this->host];')->ln()
                    ->lt()->concat('}')->ln()->ln()
                    ->lt()->concat('public function onOpen(ConnectionInterface $conn): void')->ln()->lt()->concat('{')
                    ->ln()->lt()->lt()->concat('echo("New connection! ({$conn->resourceId})\n");')->ln()
                    ->lt()->lt()->concat('$this->clients->attach($conn);')->ln()
                    ->lt()->concat('}')->ln()->ln()
                    ->lt()->concat('public function onMessage(ConnectionInterface $from, $msg): void')->ln()->lt()
                    ->concat('{')->ln()->lt()->lt()->concat('foreach ($this->clients as $client) {')->ln()
                    ->lt()->lt()->lt()->concat('if ($from !== $client) {')->ln()
                    ->lt()->lt()->lt()->lt()->concat('$client->send($msg);')->ln()
                    ->lt()->lt()->lt()->concat('}')->ln()
                    ->lt()->lt()->concat('}')->ln()
                    ->lt()->concat('}')->ln()->ln()
                    ->lt()->concat('public function onClose(ConnectionInterface $conn): void')->ln()->lt()->concat('{')
                    ->ln()->lt()->lt()->concat('$this->clients->detach($conn);')->ln()
                    ->lt()->concat('}')->ln()->ln()
                    ->lt()->concat('public function onError(ConnectionInterface $conn, Exception $e): void')->ln()
                    ->lt()->concat('{')->ln()->lt()->lt()->concat('$conn->close();')->ln()->lt()->concat('}')->ln()
                    ->concat('}')->ln()
                    ->get()
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SOCKET: {$socket}"));
        $output->writeln($this->successOutput("\t>>  SOCKET: the '{$listFactory['namespace']}\\{$listFactory['class']}' socket has been generated"));

        return Command::SUCCESS;
	}
}
